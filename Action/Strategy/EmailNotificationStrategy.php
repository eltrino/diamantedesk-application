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
use Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy\EmailNotification;
use Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy\EmailTemplate;
use Diamante\AutomationBundle\Model\ListedEntity\ListedEntitiesProvider;
use Diamante\AutomationBundle\Model\ListedEntity\ProcessorInterface;
use Diamante\AutomationBundle\Rule\Action\ActionStrategy;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Api\Internal\UserServiceImpl;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class EmailNotificationStrategy implements ActionStrategy, NotificationStrategy
{
    const TYPE = 'notifyByEmail';

    const EMAIL_NOTIFIER_CONFIG_PATH = 'oro_notification.email_notification_sender_email';

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
     * @var Container
     */
    protected $container;

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
     * @param Container $container
     * @param UserServiceImpl $userService
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(
        Container $container,
        UserServiceImpl $userService
    ) {
        $this->userService = $userService;
        $this->container = $container;

        $this->notification = new EmailNotification($this->userService);

        $this->doctrineRegistry = $container->get('doctrine');
        $this->listedEntitiesProvider = $container->get('diamante_automation.provider.listed_entities');
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
        $t = 1;
    }

    /**
     * @param ExecutionContext $context
     */
    public function prepareRecipientsList(ExecutionContext $context)
    {
        $this->notification->setContext($context);
        $this->recipientsList = $this->notification->getRecipientEmails();
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
//        if (!$this->container->isScopeActive('request')) {
//            $this->container->enterScope('request');
//            $this->container->set('request', new Request(), 'request');
//        }
//
//        $ticket = $this->loadTicket($notification);
//        $changeList = $this->postProcessChangesList($notification);
//
//
//        foreach ($this->watchersService->getWatchers($ticket) as $watcher) {
//            $userType = $watcher->getUserType();
//            $user = User::fromString($userType);
//            $isOroUser = $user->isOroUser();
//            if ($isOroUser) {
//                $loadedUser = $this->oroUserManager->findUserBy(['id' => $user->getId()]);
//            } else {
//                $loadedUser = $this->diamanteUserRepository->get($user->getId());
//            }
//
//            if (!$isOroUser && $notification->isTagUpdated()) {
//                continue;
//            }
//
//            $message = $this->message($notification, $ticket, $isOroUser, $loadedUser->getEmail(), $changeList);
//            $this->mailer->send($message);
//            $reference = new MessageReference($message->getId(), $ticket);
//            $this->messageReferenceRepository->store($reference);
//        }
    }

    /**
     * @param UniqueId $uniqueId
     * @return Ticket
     */
    private function loadTicket(UniqueId $uniqueId)
    {
        /** @var TicketRepository $repository */
        $repository = $this->doctrineRegistry->getRepository('DiamanteDeskBundle:Ticket');
        $ticket = $repository->getByUniqueId($uniqueId);
        return $ticket;
    }
}