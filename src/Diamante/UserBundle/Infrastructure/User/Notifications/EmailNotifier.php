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
namespace Diamante\UserBundle\Infrastructure\User\Notifications;

use Diamante\DeskBundle\Model\Ticket\Notifications\Notifier;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Diamante\DeskBundle\Model\Shared\Notification;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Diamante\DeskBundle\Model\Shared\Email\TemplateResolver;

class EmailNotifier implements Notifier
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TemplateResolver
     */
    private $templateResolver;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var NameFormatter
     */
    private $nameFormatter;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var string
     */
    private $senderEmail;

    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TemplateResolver $templateResolver,
        UserService $userService,
        NameFormatter $nameFormatter,
        ConfigManager $configManager,
        $senderEmail
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->templateResolver = $templateResolver;
        $this->userService = $userService;
        $this->nameFormatter = $nameFormatter;
        $this->configManager = $configManager;
        $this->senderEmail = $senderEmail;
    }

    /**
     * @param Notification $notification
     *
     * @return void
     */
    public function notify(Notification $notification)
    {
        $message = $this->message($notification);
        $this->mailer->send($message);
    }

    /**
     * @param Notification $notification
     *
     * @return \Swift_Message
     */
    private function message(Notification $notification)
    {
        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        $message->setSubject(ucfirst($notification->getHeaderText()));
        $senderName = $this->configManager->get('oro_notification.email_notification_sender_name');
        $message->setFrom($this->senderEmail, $senderName);
        $mail = $notification->getAuthor()->getEmail();
        $message->setTo($mail);

        $options = array(
            'user'          => $this->getFormattedUserName($notification),
            'header'        => $notification->getHeaderText(),
        );

        $txtTemplate = $this->templateResolver->resolve($notification, TemplateResolver::TYPE_TXT);
        $htmlTemplate = $this->templateResolver->resolve($notification, TemplateResolver::TYPE_HTML);

        $message->setBody($this->twig->render($txtTemplate, $options), 'text/plain');
        $message->addPart($this->twig->render($htmlTemplate, $options), 'text/html');

        return $message;
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    private function getFormattedUserName(Notification $notification)
    {
        $user = $notification->getAuthor();
        $userId = $this->userService->verifyDiamanteUserExists($user->getEmail());
        $user = empty($userId) ? $user : new User($userId, User::TYPE_DIAMANTE);
        $user = $this->userService->getByUser($user);
        $format = $this->nameFormatter->getNameFormat();

        $name = str_replace(
            array('%first_name%', '%last_name%', '%prefix%', '%middle_name%', '%suffix%'),
            array($user->getFirstName(), $user->getLastName(), '', '', ''),
            $format
        );

        return trim($name);
    }
}