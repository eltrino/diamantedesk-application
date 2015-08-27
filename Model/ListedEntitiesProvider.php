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

namespace Diamante\AutomationBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ListedEntitiesProvider
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $entities;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function provideListedEntities()
    {
        if (!$this->entities) {
            $this->entities = $this->container->getParameter('diamante.automation.listed_entities');

            foreach ($this->entities as $class) {
                if (!class_exists($class)) {
                    throw new \RuntimeException(sprintf('Listed class doe\'s not exists: %s', $class));
                }
            }
        }

        return $this->entities;
    }
}