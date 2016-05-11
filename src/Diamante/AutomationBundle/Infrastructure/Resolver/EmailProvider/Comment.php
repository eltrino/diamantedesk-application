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
     * @var UserManager
     */
    protected $oroUserManager;

    /**
     * Comment constructor.
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
    public function getAuthor(array $target)
    {

        $user = $this->userService->getByUser($target['author']);

        return $user->getEmail();
    }

    /**
     * @param array $target
     *
     * @return string
     */
    public function getReporter(array $target)
    {
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        $ticket = $target['ticket'];
        $user = $this->userService->getByUser($ticket->getReporter());

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
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        $ticket = $target['ticket'];
        $watchers = $ticket->getWatcherList();

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
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        $ticket = $target['ticket'];
        /** @var array|DiamanteUser $assignee */
        $assignee = $ticket->getAssignee();

        if (!empty($assignee)) {
            if ($assignee instanceof OroUser) {
                $user = $this->oroUserManager->findUserBy(array('id' => $assignee->getId()));
                return $user->getEmail();
            }
            return $assignee->getEmail();
        }

        return null;
    }

    public function getName()
    {
        return 'comment';
    }

}