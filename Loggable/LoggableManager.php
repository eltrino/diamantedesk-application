<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Diamante\DeskBundle\Loggable;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use  Oro\Bundle\DataAuditBundle\Loggable\LoggableManager as OroLoggableManager;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\DataAuditBundle\Loggable\AuditEntityMapper;

class LoggableManager extends OroLoggableManager
{

    protected $container;

    public function __construct(
        $logEntityClass,
        $logEntityFieldClass,
        ConfigProvider $auditConfigProvider,
        ServiceLink $securityContextLink,
        AuditEntityMapper $auditEntityMapper,
        ContainerInterface $container
    ) {
        parent::__construct(
            $logEntityClass,
            $logEntityFieldClass,
            $auditConfigProvider,
            $securityContextLink,
            $auditEntityMapper
        );
        $this->container = $container;
    }

    /**
     * @param string $action
     * @param mixed  $entity
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \ReflectionException
     */
    protected function createLogEntity($action, $entity)
    {
        $entityClassName = $this->getEntityClassName($entity);
        if (!$this->checkAuditable($entityClassName)) {
            return;
        }

        $uow = $this->em->getUnitOfWork();

        $meta = $this->getConfig($entityClassName);
        $entityMeta = $this->em->getClassMetadata($entityClassName);

        $logEntryMeta = $this->em->getClassMetadata($this->getLogEntityClass());
        $user = $this->container->get('diamante.user.service')->getByUser($entity->getOwner());
        $organization = current($this->em->getRepository('OroOrganizationBundle:Organization')->getEnabled());

        /** @var Audit $logEntry */
        $logEntry = $logEntryMeta->newInstance();
        $logEntry->setAction($action);
        $logEntry->setObjectClass($meta->name);
        $logEntry->setLoggedAt();
        $logEntry->setUser($user);
        $logEntry->setOrganization($organization);
        $logEntry->setObjectName(method_exists($entity, '__toString') ? $entity->__toString() : $meta->name);

        $entityId = $this->getIdentifier($entity);

        if (!$entityId && $action === self::ACTION_CREATE) {
            $this->pendingLogEntityInserts[spl_object_hash($entity)] = $logEntry;
        }

        $logEntry->setObjectId($entityId);

        $newValues = array();

        if ($action !== self::ACTION_REMOVE && count($meta->propertyMetadata)) {
            foreach ($uow->getEntityChangeSet($entity) as $field => $changes) {
                if (!isset($meta->propertyMetadata[$field])) {
                    continue;
                }

                $old = $changes[0];
                $new = $changes[1];

                if ($old == $new) {
                    continue;
                }

                $fieldMapping = null;
                if ($entityMeta->hasField($field)) {
                    $fieldMapping = $entityMeta->getFieldMapping($field);
                    if ($fieldMapping['type'] == 'date') {
                        // leave only date
                        $utc = new \DateTimeZone('UTC');
                        if ($old && $old instanceof \DateTime) {
                            $old->setTimezone($utc);
                            $old = new \DateTime($old->format('Y-m-d'), $utc);
                        }
                        if ($new && $new instanceof \DateTime) {
                            $new->setTimezone($utc);
                            $new = new \DateTime($new->format('Y-m-d'), $utc);
                        }
                    }
                }

                if ($old instanceof \DateTime && $new instanceof \DateTime
                    && $old->getTimestamp() == $new->getTimestamp()
                ) {
                    continue;
                }

                if ($entityMeta->isSingleValuedAssociation($field) && $new) {
                    $oid = spl_object_hash($new);
                    $value = $this->getIdentifier($new);

                    if (!is_array($value) && !$value) {
                        $this->pendingRelatedEntities[$oid][] = array(
                            'log'   => $logEntry,
                            'field' => $field
                        );
                    }

                    $method = $meta->propertyMetadata[$field]->method;
                    if ($old !== null) {
                        // check if an object has the required method to avoid a fatal error
                        if (!method_exists($old, $method)) {
                            throw new \ReflectionException(
                                sprintf('Try to call to undefined method %s::%s', get_class($old), $method)
                            );
                        }
                        $old = $old->{$method}();
                    }
                    if ($new !== null) {
                        // check if an object has the required method to avoid a fatal error
                        if (!method_exists($new, $method)) {
                            throw new \ReflectionException(
                                sprintf('Try to call to undefined method %s::%s', get_class($new), $method)
                            );
                        }
                        $new = $new->{$method}();
                    }
                }

                $newValues[$field] = array(
                    'old'  => $old,
                    'new'  => $new,
                    'type' => $this->getFieldType($entityMeta, $field),
                );
            }

            $entityIdentifier = $this->getEntityIdentifierString($entity);
            if (!empty($this->collectionLogData[$entityClassName][$entityIdentifier])) {
                $collectionData = $this->collectionLogData[$entityClassName][$entityIdentifier];
                foreach ($collectionData as $field => $changes) {
                    if (!isset($meta->propertyMetadata[$field])) {
                        continue;
                    }

                    if ($changes['old'] != $changes['new']) {
                        $newValues[$field] = $changes;
                        $newValues[$field]['type'] = $this->getFieldType($entityMeta, $field);
                    }
                }
            }

            foreach ($newValues as $field => $newValue) {
                $logEntry->createField($field, $newValue['type'], $newValue['new'], $newValue['old']);
            }
        }

        if ($action === self::ACTION_UPDATE && 0 === count($newValues)) {
            return;
        }

        $version = 1;

        if ($action !== self::ACTION_CREATE) {
            $version = $this->getNewVersion($logEntryMeta, $entity);

            if (empty($version)) {
                // was versioned later
                $version = 1;
            }
        }

        $logEntry->setVersion($version);

        $this->em->persist($logEntry);
        $uow->computeChangeSet($logEntryMeta, $logEntry);

        $logEntryFieldMeta = $this->em->getClassMetadata($this->logEntityFieldClass);
        foreach ($logEntry->getFields() as $field) {
            $this->em->persist($field);
            $uow->computeChangeSet($logEntryFieldMeta, $field);
        }
    }

    /**
     * @param               $entity
     * @param EntityManager $em
     */
    public function handlePostPersist($entity, EntityManager $em)
    {
        $this->em = $em;
        $uow = $em->getUnitOfWork();
        $oid = spl_object_hash($entity);

        if ($this->pendingLogEntityInserts && array_key_exists($oid, $this->pendingLogEntityInserts)) {
            $logEntry = $this->pendingLogEntityInserts[$oid];
            $logEntryMeta = $em->getClassMetadata(ClassUtils::getClass($logEntry));

            $id = $this->getIdentifier($entity);
            $logEntryMeta->getReflectionProperty('objectId')->setValue($logEntry, $id);

            $uow->scheduleExtraUpdate(
                $logEntry,
                array(
                    'objectId' => array(null, $id)
                )
            );
            $uow->setOriginalEntityProperty(spl_object_hash($logEntry), 'objectId', $id);

            unset($this->pendingLogEntityInserts[$oid]);
        }

        if ($this->pendingRelatedEntities && array_key_exists($oid, $this->pendingRelatedEntities)) {
            $identifiers = $uow->getEntityIdentifier($entity);

            foreach ($this->pendingRelatedEntities[$oid] as $props) {
                /** @var Audit $logEntry */
                $logEntry = $props['log'];
                $data = $logEntry->getData();
                if (empty($data[$props['field']]['new'])) {
                    $data[$props['field']]['new'] = implode(', ', $identifiers);
                    $oldField = $logEntry->getField($props['field']);
                    $logEntry->createField(
                        $oldField->getField(),
                        $oldField->getDataType(),
                        $data[$props['field']]['new'],
                        $oldField->getOldValue()
                    );

                    $uow->computeChangeSet($logEntryMeta, $logEntry);
                    $uow->setOriginalEntityProperty(spl_object_hash($logEntry), 'objectId', $data);
                }
            }

            unset($this->pendingRelatedEntities[$oid]);
        }
    }

    /**
     * @param $entity
     *
     * @return string
     */
    private function getEntityClassName($entity)
    {
        if (is_object($entity)) {
            return ClassUtils::getClass($entity);
        }

        return $entity;
    }

    /**
     * @param DoctrineClassMetadata $entityMeta
     * @param string                $field
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getFieldType(DoctrineClassMetadata $entityMeta, $field)
    {
        $type = null;
        if ($entityMeta->hasField($field)) {
            $type = $entityMeta->getTypeOfField($field);
            if ($type instanceof Type) {
                $type = $type->getName();
            }
        } elseif ($entityMeta->hasAssociation($field)) {
            $type = Type::STRING;
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Field "%s" is not mapped field of "%s" entity.',
                    $field,
                    $entityMeta->getName()
                )
            );
        }

        return $type;
    }
}
