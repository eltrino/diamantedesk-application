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

namespace Diamante\AutomationBundle\Infrastructure\Shared;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ChangesetBuilder
 *
 * @package Diamante\AutomationBundle\Infrastructure\Shared
 */
class ChangesetBuilder
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ChangesetBuilder constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        $oldProps = $this->getUpdatedProperties($entity);
        $changeset = [];
        $reflect = new \ReflectionClass($entity);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        foreach ($props as $refProperty) {
            $refProperty->setAccessible(true);
            $name = $refProperty->getName();
            $new = $refProperty->getValue($entity);
            $old = $new;

            if(array_key_exists($name, $oldProps)) {
                $old = $oldProps[$name];
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
            $changeset[$name] = [$value, null];
        }

        return $changeset;
    }

    /**
     * @param $entity
     *
     * @return array
     */
    private function getUpdatedProperties($entity)
    {
        $properties = [];
        $uow = $this->container->get('doctrine.orm.entity_manager')->getUnitOfWork();
        $changeset = $uow->getEntityChangeSet($entity);

        foreach($changeset as $property => $values) {
            list($old, $new) = $values;

            if ($old != $new) {
                $properties[$property] = $old;
            }
        }

        return $properties;
    }
}