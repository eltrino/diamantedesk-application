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
namespace Eltrino\EmailProcessingBundle\Infrastructure\Message\Zend;

use Eltrino\EmailProcessingBundle\Model\Message;
use Eltrino\EmailProcessingBundle\Model\Message\MessageProvider;
use Eltrino\EmailProcessingBundle\Model\MessageProcessingException;

class RawMessageProvider implements MessageProvider
{
    private $input;

    /**
     * @var MessageConverter
     */
    private $converter;

    public function __construct($input, MessageConverter $converter)
    {
        $this->validate($input);
        $this->input           = $input;
        $this->converter       = $converter;
    }

    private function validate($input)
    {
        if (false === is_string($input)) {
            throw new MessageProcessingException('Input raw message should be a string.');
        }
    }

    /**
     * Fetch messages that should be processed
     * @return array|Message[]
     * @throws MessageProcessingException
     */
    public function fetchMessagesToProcess()
    {
        $zendMailMessage = $this->converter->fromRawMessage($this->input);

        $headers            = $zendMailMessage->getHeaders();

        $uniqueMessageId    = uniqid($zendMailMessage->getSubject());
        $messageId          = $this->processMessageId($headers);
        $messageSubject     = $this->processSubject($headers);
        $messageContent     = $this->processContent($zendMailMessage);
        $messageReference   = $this->processMessageReference($headers);
        $messageAttachments = $this->processAttachments($zendMailMessage);


        $message = new Message($uniqueMessageId, $messageId, $messageSubject, $messageContent,
            $messageReference, $messageAttachments);
        return array($message);
    }

    /**
     * Mark given messages as Processed
     * @param array $messages
     * @return void
     */
    public function markMessagesAsProcessed(array $messages)
    {
        // not implemented
    }
}
