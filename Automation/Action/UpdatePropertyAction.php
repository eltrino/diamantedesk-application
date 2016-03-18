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

namespace Diamante\AutomationBundle\Automation\Action;

use Diamante\AutomationBundle\Rule\Action\AbstractAction;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Model\Shared\Updatable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;


class UpdatePropertyAction extends AbstractAction
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AutomationConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry, AutomationConfigurationProvider $configurationProvider)
    {
        $this->registry = $registry;
        $this->configurationProvider = $configurationProvider;
    }

    public function execute()
    {
        $context = $this->getContext();
        $target = $context->getFact()->getTarget();
        $targetType = $context->getFact()->getTargetType();
        $properties = $context->getParameters()->all();
        $targetClass = $this->configurationProvider->getEntityConfiguration($targetType)->get('class');

        $entity = $this->update($target, $targetClass, $properties);

        $this->registry->getManager()->persist($entity);
    }

    protected function getAccessorForProperty($property, $target)
    {
        $reflection = new \ReflectionClass($target);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $setterNames = [
            sprintf("set%s", ucwords($property)),
            sprintf("update%s", ucwords($property))
        ];

        foreach ($methods as $method) {
            if (in_array($method->getName(), $setterNames)) {
                return $method->getName();
            }
        }

        throw new \RuntimeException(sprintf("Given target has no publicly available setter for property %s", $property));
    }

    protected function update(array $target, $targetClass, $properties)
    {
        $targetEntity = new \ReflectionClass($targetClass);

        if ($targetEntity->hasMethod('updateProperties')) {
            /** @var DoctrineGenericRepository $repository */
            $repository = $this->registry->getManager()->getRepository($targetClass);
            /** @var Updatable $entity */
            $entity = $repository->get($target['id']);
            $entity->updateProperties($properties);

            return $entity;
        }

        /**
         * External entities
         * @TODO add support of array target
         */
        foreach ($properties as $property => $value) {
            if (property_exists($target, $property)) {
                $accessorMethod = $this->getAccessorForProperty($property, $target);

                call_user_func_array([$target, $accessorMethod], [$value]);
            }
        }

        return $this;
    }
}