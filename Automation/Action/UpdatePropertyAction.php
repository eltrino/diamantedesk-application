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
use Doctrine\Bundle\DoctrineBundle\Registry;

class UpdatePropertyAction extends AbstractAction
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function execute()
    {
        $context = $this->getContext();
        $target = $context->getFact()->getTarget();
        $properties = $context->getParameters()->all();

        $this->update($target, $properties);

        $this->registry->getManager()->persist($target);
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

    protected function update($target, $properties)
    {
        if (method_exists($target, 'updateProperties')) {
            call_user_func([$target, 'updateProperties'], $properties);
            return;
        }

        foreach ($properties as $property => $value) {
            if (property_exists($target, $property)) {
                $accessorMethod = $this->getAccessorForProperty($property, $target);

                call_user_func_array([$target, $accessorMethod], [$value]);
            }
        }
    }
}