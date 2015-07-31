<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Action\Strategy;

use Diamante\AutomationBundle\Action\NotificationStrategy;
use Diamante\AutomationBundle\Rule\Action\ActionStrategy;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\EmailProcessingBundle\Model\Message\MessageRecipient;
use Diamante\UserBundle\Api\Internal\UserServiceImpl;

class EmailNotificationStrategy implements ActionStrategy, NotificationStrategy
{
    const RECIPIENTS = 'recipients';
    const TYPE    = 'notifyByEmail';

    const TEMPLATE_TYPE_HTML = 1;
    const TEMPLATE_TYPE_TXT = 2;

    /**
     * recipients in format email => name
     * @var array
     */
    protected $recipientsList = [];

    /** @var array */
    protected $templates = [];

    /**
     * @var UserServiceImpl
     */
    protected $userService;

    /**
     * @param UserServiceImpl $userService
     */
    public function __construct(
        UserServiceImpl $userService
    ) {
        $this->userService = $userService;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @param ExecutionContext $context
     * @return bool
     */
    public function isApplicable(ExecutionContext $context)
    {
        return self::TYPE === $context->getActionType();
    }

    /**
     * @param ExecutionContext $context
     */
    public function execute(ExecutionContext $context)
    {
        $this->prepareRecipientsList($context);
        $this->resolveNotificationTemplates();
        $t = 1;
    }

    /**
     * @param ExecutionContext $context
     */
    public function prepareRecipientsList(ExecutionContext $context)
    {
        $arguments = $context->getActionArguments();
        if (!property_exists($arguments, static::RECIPIENTS)) {
            return;
        }

        $recipients = $arguments->{static::RECIPIENTS};

        foreach ($recipients as $email) {
            $this->recipientsList[$email] = $this->getUserName($email);
        }
    }

    /**
     * @return array
     */
    public function resolveNotificationTemplates()
    {
        if (empty($this->templates)) {
            $this->templates = [
                static::TEMPLATE_TYPE_TXT  => 'DiamanteDeskBundle:Ticket/notification:notification.txt.twig',
                static::TEMPLATE_TYPE_HTML => 'DiamanteDeskBundle:Ticket/notification:notification.html.twig',
            ];
        }

        return $this->templates;
    }

    public function notify()
    {
        // TODO: Implement notify() method.
    }

    /**
     * @param $email
     * @return string
     */
    private function getUserName($email)
    {
        $user = $this->userService->getUserByEmail($email);
        if ($user) {
            $userDetails = $this->userService->fetchUserDetails($user);
            return sprintf(
                "%s %s",
                $userDetails->getFirstName(),
                $userDetails->getLastName()
            );
        }
        $recipient = new MessageRecipient($email, null);
        return sprintf(
            "%s %s",
            $recipient->getFirstName(),
            $recipient->getLastName()
        );
    }
}