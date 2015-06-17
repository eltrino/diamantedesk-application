<?php

namespace Diamante\AutomationBundle\Action;

use Diamante\AutomationBundle\Rule\Action\ExecutionContext;

interface NotificationStrategy
{
    public function prepareRecipientsList(ExecutionContext $context);
    public function resolveNotificationTemplates();
    public function notify();
    public function getNotificationChannel();
}