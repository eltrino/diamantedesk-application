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

namespace Eltrino\DiamanteDeskBundle\Model\Ticket;

use Doctrine\Common\Collections\ArrayCollection;
use Eltrino\DiamanteDeskBundle\Attachment\Model\Attachment;
use Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentHolder;
use Eltrino\DiamanteDeskBundle\Model\Shared\Entity;
use Oro\Bundle\UserBundle\Entity\User;

class Comment implements Entity, AttachmentHolder
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * Comment content
     * @var string $text
     */
    protected $content;

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @var User
     */
    protected $author;

    /**
     * @var ArrayCollection
     */
    protected $attachments;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct($content, $ticket, $author)
    {
        $this->content = $content;
        $this->ticket = $ticket;
        $this->author = $author;
        $this->attachments = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Update content
     * @param string $content
     */
    public function updateContent($content)
    {
        $this->content = $content;
    }

    /**
     * @param Attachment $attachment
     * @return void
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments->add($attachment);
    }

    public function getAttachments()
    {
        return new ArrayCollection($this->attachments->toArray());
    }

    /**
     * Retrieves Attachment
     * @param $attachmentId
     * @return Attachment
     */
    public function getAttachment($attachmentId)
    {
        $attachment = $this->attachments->filter(function($elm) use ($attachmentId) {
            return $elm->getId() == $attachmentId;
        })->first();
        return $attachment;
    }

    public function removeAttachment(Attachment $attachment)
    {
        $this->attachments->remove($attachment->getId());
    }
}
