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

namespace Eltrino\EmailProcessingBundle\Infrastructure\Message;

use Eltrino\EmailProcessingBundle\Model\Message;

class MessageFactory
{
    /**
     * @param $uniqueMessageId
     * @param \Zend\Mail\Storage\Message $message
     * @return Message
     */
    public function create($uniqueMessageId, \Zend\Mail\Storage\Message $message)
    {
        $headers   = $message->getHeaders();
        $messageId = $this->parseMessageId($headers);
        $subject   = $this->parseSubject($headers);
        $reference = $this->parseReferences($headers);

        $content   = $this->parseContent($message);

        return new Message($uniqueMessageId, $messageId, $subject, $content, $reference);
    }

    /**
     * @param $headers
     * @return string|null
     */
    private function parseMessageId($headers)
    {
        $id = null;
        if ($headers->get('messageid')) {
            preg_match('/<([^<]+)>/', $headers->get('messageid')->getFieldValue(), $matches);
            $id = $matches[1];
        }

        return $id;
    }

    /**
     * @param $headers
     * @return string|null
     */
    private function parseSubject($headers)
    {
        return $headers->get('subject') ? $headers->get('subject')->getFieldValue() : null;
    }

    /**
     * @param $message
     * @return string
     */
    private function parseContent($message)
    {
        if ($message->isMultipart()) {
            return $message->getPart(1);
        }

        return $message->getContent();
    }

    /**
     * @param $headers
     * @return string|null
     */
    private function parseReferences($headers)
    {
        $reference = null;
        if ($headers->get('references')) {
            preg_match('/<([^<]+)>/', $headers->get('references')->getFieldValue(), $matches);
            $reference = $matches[1];
        }

        return $reference;
    }
}