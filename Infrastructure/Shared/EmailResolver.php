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

use Diamante\UserBundle\Api\UserService;

/**
 * Class EmailResolver
 *
 * @package Diamante\AutomationBundle\Infrastructure\Shared
 */
class EmailResolver
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * EmailResolver constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getList($target, array $parameters)
    {
        $list = [];
        foreach ($parameters as $item) {
            $validEmail = filter_var($item, FILTER_VALIDATE_EMAIL);
            if (!$validEmail) {
                $method = sprintf("get%s", ucwords($item));
                if (method_exists($this, $method)) {
                    $validEmail = call_user_func([$this, $method], $target);
                } else {
                    throw new \RuntimeException('Invalid email constant.');
                }
            }

            if (is_array($validEmail)) {
                $list = array_merge($list, $validEmail);
            } else {
                $list[] = $validEmail;
            }
        }

        return array_unique($list);
    }

    /**
     * @param array $target
     *
     * @return string
     */
    private function getReporter(array $target)
    {
        $user = $this->userService->getByUser($target['reporter']);

        return $user->getEmail();
    }

    /**
     * @param array $target
     *
     * @return string
     */
    private function getAuthor(array $target)
    {
        $user = $this->userService->getByUser($target['author']);

        return $user->getEmail();
    }

    /**
     * @param array $target
     *
     * @return array
     */
    private function getWatchers(array $target)
    {
        return ['test@mail.com', 'test1@mail.com'];
    }

    /**
     * @param array $target
     *
     * @return string
     */
    private function getAssignee(array $target)
    {
        $user = $this->userService->getByUser($target['assignee']);

        return $user->getEmail();
    }
}