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

use Diamante\AutomationBundle\Rule\Action\AbstractAction;

class NotifyByEmailAction extends AbstractAction
{
    const ACTION_NAME = 'notify_by_email';

    /**
     * @var EntityNotifier[]
     */
    private $notifiers = [];

    /**
     * @param EntityNotifier $notifier
     */
    public function addEmailNotifier(EntityNotifier $notifier)
    {
        $this->notifiers[$notifier->getName()] = $notifier;
    }

    public function execute()
    {
        $context = $this->getContext();
        $targetType = $context->getFact()->getTargetType();

        $this->notifiers[$targetType]->setContext($context);
        $this->notifiers[$targetType]->notify();
    }

    /**
     * @param array $parameters
     */
    public function addParameters(array $parameters)
    {
        $this->getContext()->addParameter($parameters[static::ACTION_NAME]);
    }
}