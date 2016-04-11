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

namespace Diamante\AutomationBundle\Rule\Action;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\EntityBundle\Event\OroEventManager;

/**
 * Class AbstractModifyAction
 *
 * @package Diamante\AutomationBundle\Rule\Action
 */
abstract class AbstractModifyAction extends AbstractAction
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function setRegistry(Registry $registry)
    {
        $this->registry = $registry;
        $this->em = $registry->getManager();
    }

    /**
     * @param array $parameters
     */
    public function addParameters(array $parameters)
    {
        $this->getContext()->addParameters($parameters);
    }

    /**
     * to avoid update action entry in PersistentProcessingContext entity
     */
    protected function disableListeners() {
        /** @var OroEventManager $event */
        $event = $this->em->getEventManager();
        $event->disableListeners();
    }
}