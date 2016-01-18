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

namespace Diamante\AutomationBundle\MassAction\Handlers;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Exception\LogicException;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\DeletionIterableResult;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;

abstract class RuleStateMassActionHandler implements MassActionHandlerInterface
{
    const FLUSH_BATCH_SIZE = 100;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param EntityManager       $entityManager
     * @param TranslatorInterface $translator
     * @param SecurityFacade      $securityFacade
     */
    public function __construct(
        EntityManager $entityManager,
        TranslatorInterface $translator,
        SecurityFacade $securityFacade
    ) {
        $this->entityManager  = $entityManager;
        $this->translator     = $translator;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritDoc}
     */
    public function handleState(MassActionHandlerArgs $args, $callback)
    {
        $iteration             = 0;
        $entityName            = null;
        $entityIdentifiedField = null;

        $results = new DeletionIterableResult($args->getResults()->getSource());
        $results->setBufferSize(self::FLUSH_BATCH_SIZE);

        // batch state should be processed in transaction
        $this->entityManager->beginTransaction();
        try {
            set_time_limit(0);

            foreach ($results as $result) {
                /** @var $result ResultRecordInterface */
                $entity = $result->getRootEntity();
                if (!$entity) {
                    // no entity in result record, it should be extracted from DB
                    if (!$entityName) {
                        $entityName = $this->getEntityName($args);
                    }
                    if (!$entityIdentifiedField) {
                        $entityIdentifiedField = $this->getEntityIdentifierField($args);
                    }
                    $entity = $this->getEntity($entityName, $result->getValue($entityIdentifiedField));
                }

                if ($entity) {
                    if ($this->securityFacade->isGranted('EDIT', $entity)) {
                        $callback($entity);
                        $this->entityManager->persist($entity);
                        $iteration++;
                    }

                    if ($iteration % self::FLUSH_BATCH_SIZE == 0) {
                        $this->finishBatch();
                    }
                }
            }

            if ($iteration % self::FLUSH_BATCH_SIZE > 0) {
                $this->finishBatch();
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $this->getResponse($args, $iteration);
    }

    /**
     * Finish processed batch
     */
    protected function finishBatch()
    {
        $this->entityManager->flush();
        if ($this->entityManager->getConnection()->getTransactionNestingLevel() == 1) {
            $this->entityManager->clear();
        }
    }

    /**
     * @param MassActionHandlerArgs $args
     * @param int                   $entitiesCount
     *
     * @return MassActionResponse
     */
    protected function getResponse(MassActionHandlerArgs $args, $entitiesCount = 0)
    {
        $massAction      = $args->getMassAction();
        $responseMessage = $massAction->getOptions()->offsetGetByPath('[messages][success]', static::RESPONSE_MESSAGE);

        $successful = $entitiesCount > 0;
        $options    = ['count' => $entitiesCount];

        return new MassActionResponse(
            $successful,
            $this->translator->transChoice(
                $responseMessage,
                $entitiesCount,
                ['%count%' => $entitiesCount]
            ),
            $options
        );
    }

    /**
     * @param MassActionHandlerArgs $args
     *
     * @return string
     * @throws LogicException
     */
    protected function getEntityName(MassActionHandlerArgs $args)
    {
        $massAction = $args->getMassAction();
        $entityName = $massAction->getOptions()->offsetGet('entity_name');
        if (!$entityName) {
            throw new LogicException(sprintf('Mass action "%s" must define entity name', $massAction->getName()));
        }

        return $entityName;
    }

    /**
     * @param MassActionHandlerArgs $args
     *
     * @throws LogicException
     * @return string
     */
    protected function getEntityIdentifierField(MassActionHandlerArgs $args)
    {
        $massAction = $args->getMassAction();
        $identifier = $massAction->getOptions()->offsetGet('data_identifier');
        if (!$identifier) {
            throw new LogicException(sprintf('Mass action "%s" must define identifier name', $massAction->getName()));
        }

        // if we ask identifier that's means that we have plain data in array
        // so we will just use column name without entity alias
        if (strpos('.', $identifier) !== -1) {
            $parts      = explode('.', $identifier);
            $identifier = end($parts);
        }

        return $identifier;
    }

    /**
     * @param string $entityName
     * @param mixed  $identifierValue
     *
     * @return object
     */
    protected function getEntity($entityName, $identifierValue)
    {
        return $this->entityManager->getReference($entityName, $identifierValue);
    }
}
