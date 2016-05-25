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
namespace Diamante\EmailProcessingBundle\Model;

use Diamante\EmailProcessingBundle\Infrastructure\Message\Attachment;
use Diamante\EmailProcessingBundle\Model\Message\MessageSender;

class Message
{
    /**
     * @var string
     */
    private $messageId;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var MessageSender
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var array
     */
    private $recipients;

    /**
     * @var array
     */
    private $attachments;

    /**
     * @var bool
     */
    private $isFailed;

    /**
     * @var bool
     */
    private $isSystem;

    /**
     * @param               $uniqueId
     * @param               string|null $messageId
     * @param               string|null $subject
     * @param               string|null $content
     * @param MessageSender $from
     * @param               string $to
     * @param string|null          $reference
     * @param array         $attachments
     * @param bool          $isFailed
     * @param bool          $isSystem
     * @param array         $recipients
     */
    public function __construct(
        $uniqueId,
        $messageId,
        $subject,
        $content,
        MessageSender $from,
        $to,
        $reference = null,
        array $attachments = null,
        $isFailed = false,
        $isSystem = true,
        $recipients = null
    ) {
        $this->uniqueId    = $uniqueId;
        $this->messageId   = $messageId;
        $this->subject     = $subject;
        $this->content     = $content;
        $this->from        = $from;
        $this->to          = $to;
        $this->reference   = $reference;
        $this->attachments = $attachments;
        $this->isFailed    = $isFailed;
        $this->isSystem    = $isSystem;
        $this->recipients  = $recipients;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return MessageSender
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Returns attachments array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->isFailed;
    }

    /**
     * @return bool
     */
    public function isSystem()
    {
        return $this->isSystem;
    }

    /**
     * Retrieve emails of recipients
     *
     * @return array|null
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Add recipient/recipients to the message
     *
     * @param $recipients
     */
    public function addRecipients($recipients)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        foreach ($recipients as $recipient) {
            $this->recipients[] = $recipient;
        }

        $this->recipients = array_unique($this->recipients);
    }
}
