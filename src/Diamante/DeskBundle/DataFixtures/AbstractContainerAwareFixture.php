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

namespace Diamante\DeskBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractContainerAwareFixture extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param null|ContainerInterface $container A ContainerInterface instance
     *
     * @throws \Exception
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        if (is_null($container)) {
            throw new \Exception('Container is not set');
        }

        $this->container = $container;

        $this->init();
    }

    /**
     * @return null
     */
    abstract protected function init();
}
