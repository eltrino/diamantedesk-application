<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Infrastructure\Changeset;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ChangesetBuilder
 *
 * @package Diamante\AutomationBundle\Infrastructure\Changeset
 */
class ChangesetBuilder
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var FieldProcessorProvider
     */
    protected $fieldProcessorProvider;

    /**
     * ChangesetBuilder constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->fieldProcessorProvider = new FieldProcessorProvider();
    }

    /**
     * @param $entity
     *
     * @return array
     */
    public function getChangesetForCreateAction($entity)
    {
        $changeset = [];
        $reflect = new \ReflectionClass($entity);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

        foreach ($props as $refProperty) {
            $refProperty->setAccessible(true);
            $name = $refProperty->getName();
            $value = $refProperty->getValue($entity);
            $value = $this->fieldProcessorProvider->provideProcessor($name)->processField($value);

            $changeset[$name] = [null, $value];
        }

        return $changeset;
    }

    /**
     * @param $entity
     *
     * @return array
     */
    public function getChangesetForUpdateAction($entity)
    {
        $changeset = [];
        $uow = $this->container->get('doctrine.orm.entity_manager')->getUnitOfWork();
        $uowChangeset = $uow->getEntityChangeSet($entity);
        $reflect = new \ReflectionClass($entity);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

        foreach ($props as $refProperty) {
            $refProperty->setAccessible(true);
            $name = $refProperty->getName();
            $new = $refProperty->getValue($entity);
            $new = $this->fieldProcessorProvider->provideProcessor($name)->processField($new);
            $old = $new;

            if (array_key_exists($name, $uowChangeset)) {
                $old = $uowChangeset[$name][0];
            }

            $changeset[$name] = [$old, $new];
        }

        return $changeset;
    }

    /**
     * @param $entity
     *
     * @return array
     */
    public function getChangesetForRemoveAction($entity)
    {
        $changeset = [];
        $reflect = new \ReflectionClass($entity);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

        foreach ($props as $refProperty) {
            $refProperty->setAccessible(true);
            $name = $refProperty->getName();
            $value = $refProperty->getValue($entity);
            $value = $this->fieldProcessorProvider->provideProcessor($name)->processField($value);

            $changeset[$name] = [$value, $value];
        }

        return $changeset;
    }
}