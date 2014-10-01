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
namespace Eltrino\EmailProcessingBundle\Model;

use Eltrino\EmailProcessingBundle\Infrastructure\Message\Attachment;

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
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var array
     */
    private $attachments;

    /**
     * @param $uniqueId
     * @param $messageId
     * @param $subject
     * @param $content
     * @param $from
     * @param $to
     * @param null $reference
     * @param array $attachments
     */
    public function __construct($uniqueId, $messageId, $subject, $content, $from, $to, $reference = null, array $attachments = null)
    {
        $this->uniqueId    = $uniqueId;
        $this->messageId   = $messageId;
        $this->subject     = $subject;
        $this->content     = $content;
        $this->from        = $from;
        $this->to          = $to;
        $this->reference   = $reference;
        $this->attachments = $attachments;
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

    public function getFrom()
    {
        return $this->from;
    }

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

    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }
}