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

namespace Diamante\DeskBundle\Infrastructure\Notification;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Translation\TranslatorInterface;

class NotificationManager
{
    const TEMPLATE_TYPE_HTML            = 'html';
    const TEMPLATE_TYPE_TXT             = 'txt';

    const SENDER_EMAIL_CONFIG_PATH      = 'oro_notification.email_notification_sender_email';
    const SENDER_NAME_CONFIG_PATH       = 'oro_notification.email_notification_sender_name';

    /**
     * @var array
     */
    protected $templates = [];

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $fromEmail;

    /**
     * @var string
     */
    protected $fromName;

    /**
     * @var string
     */
    protected $toEmail;
    /**
     * @var string
     */
    protected $toName;

    /**
     * @var array
     */
    public $templateOptions = [];

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var NotificationOptionsProvider[]
     */
    protected $providers = [];

    /**
     * @var ConfigManager
     */
    protected $config;

    /**
     * @param \Twig_Environment $twig
     * @param \Swift_Mailer $mailer
     * @param TranslatorInterface $translator
     * @param ConfigManager $configManager
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TranslatorInterface $translator,
        ConfigManager $configManager
    ) {
        $this->twig         = $twig;
        $this->mailer       = $mailer;
        $this->translator   = $translator;
        $this->config       = $configManager;
    }

    /**
     * Clear instance
     */
    public function clear()
    {
        $this->subject = '';
        $this->toEmail = '';
        $this->toName = '';
        $this->fromEmail = '';
        $this->fromName = '';
        $this->templates = [];
        $this->templateOptions = [];
    }

    /**
     * @param string $path
     */
    public function addHtmlTemplate($path)
    {
        $this->templates[self::TEMPLATE_TYPE_HTML] = $path;
    }

    /**
     * @param string $path
     */
    public function addTxtTemplate($path)
    {
        $this->templates[self::TEMPLATE_TYPE_TXT] = $path;
    }

    /**
     * @param string $subject
     * @param bool $translatable
     */
    public function setSubject($subject, $translatable = false)
    {
        if ($translatable) {
            $subject = $this->translator->trans($subject);
        }

        $this->subject = $subject;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addTemplateOption($key, $value)
    {
        $this->templateOptions[$key] = $value;
    }

    /**
     * @param array $options
     */
    public function setTemplateOptions(array $options)
    {
        $this->templateOptions = $options;
    }

    public function notify()
    {

        $message = \Swift_Message::newInstance();
        $message->setSubject($this->subject);
        $message->setFrom($this->fromEmail, $this->fromName);
        $message->setTo($this->toEmail, $this->toName);

        $message->setBody($this->twig->render(
            $this->templates[self::TEMPLATE_TYPE_HTML],
            $this->templateOptions
        ), 'text/html');

        if (isset($this->templates[self::TEMPLATE_TYPE_TXT])) {
            $message->addPart($this->twig->render(
                $this->templates[self::TEMPLATE_TYPE_TXT],
                $this->templateOptions
            ), 'text/plain');

        }

        $this->mailer->send($message);

    }

    /**
     * @param string $email
     * @param null $name
     */
    public function setFrom($email, $name = null)
    {
        $this->fromEmail = $email;
        $this->fromName = $name;
    }

    /**
     * @param string $email
     * @param null $name
     */
    public function setTo($email, $name = null)
    {
        $this->toEmail = $email;
        $this->toName = $name;
    }

    /**
     * @param NotificationOptionsProvider $provider
     */
    public function addOptionsProvider(NotificationOptionsProvider $provider)
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * @param $name
     * @param $recipient
     * @param array $options
     */
    public function notifyByScenario($name, $recipient, array $options = [])
    {
        if (!array_key_exists($name, $this->providers)) {
            throw new \RuntimeException(sprintf('Option provider with name "%s" is not found or was not properly configured', $name));
        }

        $provider = $this->providers[$name];
        $provider->setRecipient($recipient);

        $templateOptions = array_merge($provider->getDefaultOptions(), $options);

        foreach ($provider->getDefaultOptions() as $option => $value) {
            if (!array_key_exists($option, $templateOptions)) {
                throw new \RuntimeException(sprintf("Required parameter %s is missing.", $option));
            }
        }

        $this->setTemplateOptions($templateOptions);

        $this->setTo($provider->getRecipientEmail(), $provider->getRecipientName());
        $this->addHtmlTemplate($provider->getHtmlTemplate());
        $this->addTxtTemplate($provider->getTxtTemplate());
        $this->setSubject($provider->getSubject(), $provider->subjectIsTranslatable());

        $this->setFrom(
            $this->config->get(self::SENDER_EMAIL_CONFIG_PATH),
            $this->config->get(self::SENDER_NAME_CONFIG_PATH)
        );

        $this->notify();
        $this->clear();
    }
}
