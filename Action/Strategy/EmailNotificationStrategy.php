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
use Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy\EmailConfigProvider;
use Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy\EmailNotification;
use Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy\EmailTemplate;
use Diamante\AutomationBundle\Model\ListedEntity\ListedEntitiesProvider;
use Diamante\AutomationBundle\Model\ListedEntity\ProcessorInterface;
use Diamante\AutomationBundle\Rule\Action\ActionStrategy;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\DeskBundle\Infrastructure\Notification\NotificationManager;
use Diamante\UserBundle\Api\Internal\UserServiceImpl;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class EmailNotificationStrategy implements ActionStrategy, NotificationStrategy
{
    const TYPE = 'notifyByEmail';

    /**
     * recipients in format email => name
     * @var array
     */
    protected $recipientsList = [];

    /**
     * @var EmailTemplate
     */
    protected $template;

    /**
     * @var Registry
     */
    protected $doctrineRegistry;

    /**
     * @var ListedEntitiesProvider
     */
    protected $listedEntitiesProvider;

    /**
     * @var ProcessorInterface
     */
    protected $listedEntityProcessor;

    /**
     * @var EmailNotification
     */
    protected $notification;

    /**
     * @var UserServiceImpl
     */
    protected $userService;

    /**
     * @var NotificationManager
     */
    protected $notificationManager;

    /**
     * @var EmailConfigProvider
     */
    protected $emailConfigProvider;

    /**
     * @param Registry $doctrineRegistry
     * @param UserServiceImpl $userService
     * @param EmailConfigProvider $emailConfigProvider
     * @param ListedEntitiesProvider $listedEntitiesProvider
     * @param NotificationManager $notificationManager
     */
    public function __construct(
        Registry $doctrineRegistry,
        UserServiceImpl $userService,
        EmailConfigProvider $emailConfigProvider,
        ListedEntitiesProvider $listedEntitiesProvider,
        NotificationManager $notificationManager
    ) {
        $this->userService = $userService;
        $this->emailConfigProvider = $emailConfigProvider;
        $this->doctrineRegistry = $doctrineRegistry;
        $this->listedEntitiesProvider = $listedEntitiesProvider;
        $this->notificationManager = $notificationManager;
        $this->notification = new EmailNotification($userService);
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
     * @throws \RuntimeException
     * @throws InvalidArgumentException
     */
    public function execute(ExecutionContext $context)
    {

        $this->listedEntityProcessor = $this->listedEntitiesProvider->getEntityProcessor($context->getTarget());

        if (!$this->listedEntityProcessor) {
            return;
        }

        $this->prepareRecipientsList($context);
        $this->resolveNotificationTemplates();
        $this->notify();
    }

    /**
     * @param ExecutionContext $context
     */
    public function prepareRecipientsList(ExecutionContext $context)
    {
        $this->notification->setContext($context);
        $this->notification->setListedEntityProcessor($this->listedEntityProcessor);
    }

    /**
     * @return array
     */
    public function resolveNotificationTemplates()
    {
        $template = new EmailTemplate();

        $templateFiles = $this->listedEntityProcessor->getEntityEmailTemplates();

        foreach ($templateFiles as $type => $file) {
            $template->addTemplateFile($type, $file);
        }

        $this->template = $template;
    }

    public function notify()
    {
        foreach ($this->notification->getRecipientEmails() as $name => $email) {

            try {
                $this->notificationManager->clear();
                $this->notificationManager->setSubject(
                    $this->listedEntityProcessor->formatEntityEmailSubject($this->notification->getContext()->getTarget())
                );
                $this->notificationManager->setFrom(
                    $this->emailConfigProvider->getSenderEmail(),
                    $this->emailConfigProvider->getSenderName()
                );
                $this->notificationManager->setTo($email, $name);
                $this->notificationManager->addHtmlTemplate(
                    $this->template->getTemplateFile(EmailTemplate::TEMPLATE_TYPE_HTML)
                );
                $this->notificationManager->addTxtTemplate(
                    $this->template->getTemplateFile(EmailTemplate::TEMPLATE_TYPE_TXT)
                );
                $this->notificationManager->setTemplateOptions(
                    $this->listedEntityProcessor->getEmailTemplateOptions($this->notification, $email)
                );
                $this->notificationManager->notify();
            } catch (\Exception $e) {
            }
        }
    }
}