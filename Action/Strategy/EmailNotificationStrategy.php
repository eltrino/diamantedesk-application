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

class EmailNotificationStrategy implements ActionStrategy, NotificationStrategy
{
    const CHANNEL = 'email';
    const TYPE    = 'notify';

    public function execute(ExecutionContext $context)
    {
        // TODO: Implement execute() method.
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function isApplicable(ExecutionContext $context)
    {
        $isOfType = self::TYPE === $context->getActionType();
        $args = $context->getActionArguments();
        $isChannelSupported = $this->getNotificationChannel() === $args->channel;

        return $isOfType && $isChannelSupported;
    }

    public function prepareRecipientsList(ExecutionContext $context)
    {
        // TODO: Implement prepareRecipientsList() method.
    }

    public function resolveNotificationTemplates()
    {
        // TODO: Implement resolveNotificationTemplates() method.
    }

    public function notify()
    {
        // TODO: Implement notify() method.
    }

    public function getNotificationChannel()
    {
        return self::CHANNEL;
    }
}