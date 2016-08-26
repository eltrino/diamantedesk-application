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
namespace Diamante\FrontBundle\Infrastructure;

use Diamante\DeskBundle\Infrastructure\Notification\NotificationManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Class RegistrationMailer
 *
 * @package Diamante\FrontBundle\Infrastructure
 */
class RegistrationMailer implements \Diamante\FrontBundle\Model\RegistrationMailer
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
     * @var ConfigManager
     */
    protected $config;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $htmlTwigTemplate;

    /**
     * @var string
     */
    private $txtTwigTemplate;

    /**
     * RegistrationMailer constructor.
     *
     * @param \Twig_Environment $twig
     * @param \Swift_Mailer     $mailer
     * @param ConfigManager     $configManager
     * @param                   $senderEmail
     * @param                   $htmlTwigTemplate
     * @param                   $txtTwigTemplate
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        ConfigManager $configManager,
        $senderEmail,
        $htmlTwigTemplate,
        $txtTwigTemplate
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->config = $configManager;
        $this->senderEmail = $senderEmail;
        $this->htmlTwigTemplate = $htmlTwigTemplate;
        $this->txtTwigTemplate  = $txtTwigTemplate;
    }

    /**
     * Sends confirmation email
     * @param string $email
     * @param string $hash
     * @return void
     */
    public function sendConfirmationEmail($email, $hash)
    {
        /** @var \Swift_Message $confirmation */
        $confirmation = $this->mailer->createMessage();
        $confirmation->setSubject('Confirmation');
        $confirmation->setFrom(
            $this->config->get(NotificationManager::SENDER_EMAIL_CONFIG_PATH),
            $this->config->get(NotificationManager::SENDER_NAME_CONFIG_PATH)
        );
        $confirmation->setTo($email);
        $confirmation->setReplyTo($this->senderEmail);

        $confirmation->setBody(
            $this->twig->render(
                $this->txtTwigTemplate, array('hash' => $hash)
            ), 'text/plain'
        );
        $confirmation->addPart(
            $this->twig->render(
                $this->htmlTwigTemplate, array('hash' => $hash)
            ), 'text/html'
        );

        $this->mailer->send($confirmation);
    }
}
