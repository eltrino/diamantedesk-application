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

use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\EmailProcessingBundle\Model\Message\MessageProvider;
use Diamante\EmailProcessingBundle\Model\MessageProcessingException;
use Diamante\EmailProcessingBundle\Infrastructure\Message\Attachment;
use Zend\Mime;

class RawMessageProvider extends AbstractMessageProvider implements MessageProvider
{
    private $input;

    /**
     * @var MessageConverter
     */
    private $converter;

    /**
     * @param $input
     * @param MessageConverter $converter
     */
    public function setRawStorage($input, MessageConverter $converter)
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
     * @return Message[]
     * @throws MessageProcessingException
     */
    public function fetchMessagesToProcess()
    {
        $zendMailMessage = $this->converter->fromRawMessage($this->input);

        $headers            = $zendMailMessage->getHeaders();

        $uniqueMessageId    = uniqid($zendMailMessage->getSubject());
        $messageId          = $this->processMessageId($headers);
        $messageSubject     = $zendMailMessage->getSubject();
        $messageContent     = $this->processContent($zendMailMessage);
        $messageFrom        = $this->processFrom($headers);
        $messageTo          = $this->processTo($headers);
        $messageReference   = $this->processMessageReference($headers);
        $messageAttachments = $this->processAttachments($zendMailMessage);
        $recipients = $this->processRecipients($headers);

        $message = new Message($uniqueMessageId, $messageId, $messageSubject, $messageContent,
            $messageFrom, $messageTo, $messageReference, $messageAttachments, $recipients);
        return array($message);
    }

    /**
     * Retrieves Message Content
     *
     * @param \Zend\Mail\Storage\Message $zendMailMessage
     * @return string|null
     */
    private function processContent($zendMailMessage)
    {
        $messageContent = '';

        if ($zendMailMessage->getBody()) {
            $parts = $zendMailMessage->getBody()->getParts();

            foreach ($parts as $part) {
                $split = Mime\Decode::splitContentType($part->type);
                if ($split['type'] == \Zend\Mime\Mime::TYPE_TEXT) {
                    $messageContent = $part->getContent();
                }

                if ($split['type'] == \Zend\Mime\Mime::MULTIPART_ALTERNATIVE) {
                    $boundary = $split['boundary'];
                    $bodyParts = Mime\Decode::splitMessageStruct($part->getContent(), $boundary);
                    foreach ($bodyParts as $bodyPart) {
                        $headers = $bodyPart['header'];
                        if ($headers->get('contenttype')->getType() == \Zend\Mime\Mime::TYPE_TEXT) {
                            $messageContent = $bodyPart['body'];
                        }
                    }
                }
            }
        }
        return $messageContent;
    }

    /**
     * Retrieves Message Attachments
     *
     * @param \Zend\Mail\Storage\Message $zendMailMessage
     * @return array
     */
    private function processAttachments($zendMailMessage)
    {
        $attachments = array();

        if ($zendMailMessage->getBody()) {
            $parts = $zendMailMessage->getBody()->getParts();

            foreach ($parts as $part) {
                if ($part->disposition) {
                    $disposition = \Zend\Mime\Decode::splitHeaderField($part->disposition);
                    if ($disposition[0] == \Zend\Mime\Mime::DISPOSITION_ATTACHMENT && isset($disposition['filename'])) {
                        $fileName = $disposition['filename'];
                        $fileContent = $part->getContent();
                        $attachments[] = new Attachment($fileName, base64_decode($fileContent));
                    }
                }
            }
        }
        return $attachments;
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

    /**
     * Delete processed messages
     * @param array $messages
     * @return void
     */
    public function deleteProcessedMessages(array $messages)
    {
        // not implemented
    }
}
