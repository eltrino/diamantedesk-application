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

use Diamante\EmailProcessingBundle\Model\Message\MessageSender;
use Diamante\EmailProcessingBundle\Model\Message\MessageRecipient;

abstract class AbstractMessageProvider
{
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
     * @param $headers
     * @return MessageSender
     */
    public function processFrom($headers)
    {
        $senderInfo = $headers->get('from')->getAddressList()->current();

        return new MessageSender($senderInfo->getEmail(), $senderInfo->getName());
    }

    /**
     * @param $headers
     * @return string
     */
    public function processTo($headers)
    {
        $messageTo = $headers->get('to')->getAddressList()->key();
        return $messageTo;
    }

    /**
     * @param $headers
     * @return string
     */
    public function processRecipients($headers)
    {
        $processAddressTypes = ['to', 'cc', 'from'];
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
} 