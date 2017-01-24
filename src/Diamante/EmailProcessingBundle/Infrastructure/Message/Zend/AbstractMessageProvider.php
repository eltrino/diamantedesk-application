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
namespace Diamante\EmailProcessingBundle\Infrastructure\Message\Zend;

use Symfony\Bridge\Monolog\Logger;
use Diamante\EmailProcessingBundle\Model\Message\MessageSender;
use Diamante\EmailProcessingBundle\Model\Message\MessageRecipient;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

abstract class AbstractMessageProvider
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * Retrieves Message ID
     *
     * @param \Zend\Mail\Headers $headers
     * @return string|null
     */
    protected function processMessageId($headers)
    {
        $messageId = null;
        if ($headers->get('messageid')) {
            preg_match('/<([^<]+)>/', $headers->get('messageid')->getFieldValue(), $matches);
            $messageId = $matches[1];
        }
        return $messageId;
    }

    /**
     * Do not use $headers->get('from')->getAddressList()->current() if comma use between name and surname it
     * parse email incorrect
     *
     * @param \Zend\Mail\Headers $headers
     * @return MessageSender
     */
    public function processFrom($headers)
    {
        try {
            list($name, $email) = $this->parseFrom($headers);

            return new MessageSender($email, $name);
        } catch (\RuntimeException $e) {
            $this->logger->addError($e->getMessage());
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * @param \Zend\Mail\Headers $headers
     * @return array
     */
    protected function parseFrom($headers)
    {
        $from = $headers->toArray()['From'];

        preg_match('/^((?P<name>.*?)<(?P<namedEmail>[^>]+)>|(?P<email>.+))/', $from, $matches);

        if (array_key_exists('email', $matches)) {
            $email = explode(',', $matches['email'])[0];
            $name = explode('@', $email)[0];
        } else {
            $name = $matches['name'];
            $email = $matches['namedEmail'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException(sprintf('This %s email address is considered invalid.'), $email);
        }

        return [$name, $email];
    }

    /**
     * @param \Zend\Mail\Headers $headers
     * @return string
     */
    public function processTo($headers)
    {
        $to = $headers->get('to');
        if($to) {
            $messageTo = $to->getAddressList()->key();
        } else {
            $messageTo = $this->configManager->get('diamante_email_processing.mailbox_username');
        }

        return $messageTo;
    }

    /**
     * @param \Zend\Mail\Headers $headers
     * @return string
     */
    public function processRecipients($headers)
    {
        $processAddressTypes = ['to', 'cc'];
        $recipients = [];

        foreach ($processAddressTypes as $type) {
            if (!$headers->get($type)) {
                continue;
            }
            /**
             * @var string $email
             * @var \Zend\Mail\Address $address
             */
            foreach ($headers->get($type)->getAddressList() as $email => $address) {
                $recipients[] = new MessageRecipient($address->getEmail(), null);
            }
        }

        list($name, $email) = $this->parseFrom($headers);
        $recipients[] = new MessageRecipient($email, $name);

        return $recipients;
    }

    /**
     * Retrieves Message Reference
     *
     * @param \Zend\Mail\Headers $headers
     * @return string|null
     */
    protected function processMessageReference($headers)
    {
        $messageReference = null;
        if ($headers->get('references')) {
            preg_match('/<([^<]+)>/', $headers->get('references')->getFieldValue(), $matches);
            $messageReference = $matches[1];
        }
        return $messageReference;
    }

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
} 