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

namespace Diamante\AutomationBundle\Infrastructure\Resolver;

use Diamante\AutomationBundle\Infrastructure\Resolver\EmailProvider\EntityProvider;

/**
 * Class EmailResolver
 *
 * @package Diamante\AutomationBundle\Infrastructure\Resolver
 */
class EmailResolver
{
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @param EntityProvider $provider
     */
    public function addEmailProvider(EntityProvider $provider)
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * @param        $target
     * @param string $targetType
     * @param array  $parameters
     *
     * @return array
     */
    public function getList($target, $targetType, array $parameters)
    {
        $list = [];
        foreach ($parameters as $item) {
            $email = filter_var($item, FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $method = sprintf("get%s", ucwords($item));
                if (method_exists($this->providers[$targetType], $method)) {
                    $email = $this->providers[$targetType]->$method($target);
                } else {
                    throw new \RuntimeException('Invalid email constant.');
                }
            }

            if (empty($email)) {
                continue;
            }

            if (is_array($email)) {
                $list = array_merge($list, $email);
            } else {
                array_push($list, $email);
            }
        }

        return array_unique($list);
    }
}