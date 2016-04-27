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
use Diamante\UserBundle\Entity\DiamanteUser;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Oro\Bundle\UserBundle\Entity\UserManager;

/**
 * Class Ticket
 *
 * @package Diamante\AutomationBundle\Infrastructure\Resolver\EmailProvider
 */
class Ticket implements EntityProvider
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var UserManager
     */
    protected $oroUserManager;

    /**
     * Ticket constructor.
     *
     * @param UserService $userService
     * @param UserManager $userManager
     */
    public function __construct(UserService $userService, UserManager $userManager)
    {
        $this->userService = $userService;
        $this->oroUserManager = $userManager;
    }

    /**
     * @param array $target
     *
     * @return string
     */
    public function getReporter(array $target)
    {
        $user = $this->userService->getByUser($target['reporter']);

        return $user->getEmail();
    }

    /**
     * @param array $target
     *
     * @return array
     */
    public function getWatchers(array $target)
    {
        $list = [];
        $watchers = $target['watcherList'];

        foreach ($watchers as $watcher) {
            $user = $this->userService->getByUser($watcher->getUserType());
            $list[] = $user->getEmail();
        }
        return $list;
    }

    /**
     * @param array $target
     *
     * @return string
     */
    public function getAssignee(array $target)
    {
        /** @var array|DiamanteUser $assignee */
        $assignee = $target['assignee'];
        if (!empty($assignee)) {
            if ($assignee instanceof OroUser) {
                $user = $this->oroUserManager->findUserBy(array('id' => $assignee->getId()));
                return $user->getEmail();
            }
            return $assignee->getEmail();
        }

        return null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ticket';
    }
}