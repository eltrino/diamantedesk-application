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

namespace Diamante\AutomationBundle\Model\ListedEntity;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class ListedEntitiesProvider
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ListedEntity[]|null
     */
    protected $entities;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ListedEntity[]
     * @throws \RuntimeException
     * @throws InvalidArgumentException
     */
    public function provideListedEntities()
    {
        if (!$this->entities) {
            $configData = $this->container->getParameter('diamante.automation.listed_entities');

            foreach ($configData as $record) {
                if (!class_exists($record['entity'])) {
                    throw new \RuntimeException(sprintf('Listed class doe\'s not exists: %s', $record['entity']));
                }
                if (!class_exists($record['processor'])) {
                    throw new \RuntimeException(sprintf('Processor for entity class doe\'s not exists: %s',
                        $record['processor']));
                }

                $this->entities[] = new ListedEntity($record['entity'], $record['processor']);
            }
        }

        return $this->entities;
    }

    /**
     * @param string $class
     * @return ProcessorInterface|boolean
     * @throws \RuntimeException
     * @throws InvalidArgumentException
     */
    public function getEntityProcessor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        foreach ($this->provideListedEntities() as $entity) {
            if ($entity->getListedEntityClassName() == $class) {
                return $entity->getEntityProcessor();
            }
        }

        return false;
    }
}