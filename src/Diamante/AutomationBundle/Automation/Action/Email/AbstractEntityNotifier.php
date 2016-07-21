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

namespace Diamante\AutomationBundle\Automation\Action\Email;

use Diamante\AutomationBundle\Infrastructure\Changeset\Changeset;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\AutomationBundle\Rule\Fact\AbstractFact;
use Diamante\DeskBundle\Infrastructure\Notification\NotificationManager;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Entity\DiamanteUser;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

/**
 * Class AbstractEntityNotifier
 *
 * @package Diamante\AutomationBundle\Automation\Action\Email
 */
abstract class AbstractEntityNotifier
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
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var NotificationManager
     */
    protected $notificationManager;

    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * @var AbstractFact
     */
    protected $fact;

    /**
     * @var Changeset
     */
    protected $changeset;

    /**
     * @param ExecutionContext $context
     */
    public function setContext(ExecutionContext $context)
    {
        $this->context = $context;
        $this->fact = $context->getFact();
        $this->changeset = new Changeset($this->fact->getTargetChangeset(), $this->fact->getAction());
    }

    /**
     * @return array
     */
    protected function getEmailList()
    {
        $target = $this->fact->getTarget();
        $parameters = $this->context->getParameters()->all();

        $list = [];
        foreach ($parameters as $item) {
            $email = filter_var($item, FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $method = sprintf("get%s", ucwords($item));
                if (method_exists($this, $method)) {
                    $email = static::$method($target);
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

    /**
     * Reloading oro user because it loses email after execute unserialize method
     *
     * @param OroUser|DiamanteUser $user
     *
     * @return OroUser|DiamanteUser
     *
     */
    protected function reloadUser($user)
    {
        if ($user instanceof OroUser) {
            $user = $this->oroUserManager->findUserBy(['id' => $user->getId()]);
        }

        return $user;
    }

    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param UserManager $userManager
     */
    public function setUserManager(UserManager $userManager)
    {
        $this->oroUserManager = $userManager;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param NotificationManager $notificationManager
     */
    public function setNotificationManager(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }
}