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

namespace Diamante\AutomationBundle\Infrastructure\Resolver\EmailProvider;

use Diamante\UserBundle\Api\UserService;

/**
 * Class Comment
 *
 * @package Diamante\AutomationBundle\Infrastructure\Resolver\EmailProvider
 */
class Comment implements EntityProvider
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * Comment constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param array $target
     *
     * @return string
     */
    public function getAuthor(array $target)
    {
        $user = $this->userService->getByUser($target['author']);

        return $user->getEmail();
    }

    public function getName()
    {
        return 'comment';
    }

}