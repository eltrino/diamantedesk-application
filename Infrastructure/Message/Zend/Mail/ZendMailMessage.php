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
namespace Eltrino\EmailProcessingBundle\Infrastructure\Message\Zend\Mail;
use Zend\Mail\Message;
use Zend\Mime\Decode;
use Eltrino\EmailProcessingBundle\Infrastructure\Message\Zend\Mime\ZendMimeMessage;

class ZendMailMessage extends Message
{
    /**
     * Instantiate from raw message string, Restore body to Mime\Message
     *
     * @param  string $rawMessage
     * @return Message
     */
    public static function fromString($rawMessage)
    {
        $message = new static();
        $headers = null;
        $content = null;
        Decode::splitMessage($rawMessage, $headers, $content);
        if ($headers->has('mime-version')) {
            $boundary  = $headers->get('contenttype')->getParameter('boundary');
            if ($boundary) {
                $content = ZendMimeMessage::createFromMessage($content, $boundary);
            }
        }
        $message->setHeaders($headers);
        $message->setBody($content);
        return $message;
    }
} 