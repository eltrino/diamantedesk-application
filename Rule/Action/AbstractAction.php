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

use Diamante\AutomationBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\EntityBundle\Event\OroEventManager;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var array
     */
    protected $requiredParameters;

    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $applicablePropertyTypes;

    protected function validate()
    {
        foreach ($this->requiredParameters as $parameter) {
            if (!$this->context->getParameters()->has($parameter)) {
                throw new InvalidConfigurationException(sprintf("Required parameter %s missing for action %s", $parameter, $this->name));
            }
        }

        return true;
    }

    /**
     * @param ExecutionContext $context
     */
    public function updateContext(ExecutionContext $context)
    {
        $this->context = $context;
    }

    protected function resetState()
    {
        $this->context = null;
    }

    /**
     * @return array
     */
    public function getRequiredParameters()
    {
        return $this->requiredParameters;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ExecutionContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getApplicablePropertyTypes()
    {
        return $this->applicablePropertyTypes;
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