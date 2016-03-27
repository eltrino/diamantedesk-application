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

namespace Diamante\AutomationBundle\Automation;


use Diamante\AutomationBundle\Exception\InvalidConfigurationException;
use Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag;
use Diamante\AutomationBundle\Model\Rule;
use Diamante\AutomationBundle\Rule\Action\ActionInterface;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Diamante\AutomationBundle\Entity\Action as ActionEntity;

class ActionProvider
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ParameterBag
     */
    protected $actions;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->actions   = $container->get('diamante_automation.config.provider')->getConfiguredActions();
    }

    public function getActions(Rule $rule)
    {
        $actions = [];
        foreach ($rule->getActions() as $actionDefinition) {
            $actions[] = $this->loadAction($actionDefinition);
        }

        return $actions;
    }

    /**
     * @param ActionEntity $actionDefinition
     * @return ActionInterface
     */
    protected function loadAction(ActionEntity $actionDefinition)
    {
        $service = $this->validateAndGetServiceName($actionDefinition);

        /** @var ActionInterface $action */
        $action = $this->container->get($service);

        $context = $this->prepareExecutionContext($actionDefinition);

        $action->updateContext($context);

        return $action;
    }

    /**
     * @param ActionEntity $actionEntity
     * @return string
     */
    protected function validateAndGetServiceName(ActionEntity $actionEntity)
    {
        $actionName = $actionEntity->getType();

        if (!$this->actions->has($actionName)) {
            throw new InvalidConfigurationException(sprintf("No action named '%s' found in configuration", $actionName));
        }

        $service = $this->actions->get(sprintf("%s.id", $actionName));

        if (0 !== strpos($service, '@') || empty($service)) {
            throw new InvalidConfigurationException(sprintf("Invalid configuration for service %s.", $actionName));
        }

        $service = ltrim($service, '@');

        if (!$this->container->has($service)) {
            throw new InvalidConfigurationException(
                sprintf(
                    "Invalid configuration. Action '%s' is configured to use service '%s' that does not exist",
                    $actionName,
                    $service
                )
            );
        }

        return $service;
    }

    /**
     * @param ActionEntity $actionEntity
     * @return ExecutionContext
     */
    protected function prepareExecutionContext(ActionEntity $actionEntity)
    {
        return new ExecutionContext($actionEntity->getParameters());
    }
}