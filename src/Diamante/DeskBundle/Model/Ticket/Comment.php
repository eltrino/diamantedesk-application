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

namespace Diamante\DeskBundle\Model\Ticket;

use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Model\Attachment\AttachmentHolder;
use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Shared\Owned;
use Diamante\DeskBundle\Model\Shared\Updatable;
use Diamante\UserBundle\Model\User;
use Doctrine\Common\Collections\ArrayCollection;
use Diamante\DeskBundle\Entity\Ticket as TicketEntity;

class Comment implements Entity, AttachmentHolder, Updatable, Owned
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
     * @var TicketEntity
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

    /**
     * @var boolean
     */
    protected $private;

    public function __construct($content, $ticket, $author, $private)
    {
        $this->content = $content;
        $this->ticket = $ticket;
        $this->author = $author;
        $this->attachments = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->private = $private;
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
     * @return TicketEntity
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return int
     */
    public function getTicketId()
    {
        return $this->ticket->getId();
    }

    /**
     * @return \Diamante\UserBundle\Model\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->author->getId();
    }

    /**
     * @return string
     */
    public function getAuthorType()
    {
        return $this->author->getType();
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
            /**
             * @var $elm Attachment
             */
            return $elm->getId() == $attachmentId;
        })->first();
        return $attachment;
    }

    public function removeAttachment(Attachment $attachment)
    {
        $this->attachments->removeElement($attachment);
    }

    public function delete()
    {

    }

    public function isPrivate()
    {
        return $this->private;
    }

    public function setPrivate($private)
    {
        $this->private = $private;
    }

    /**
     * Update properties of the comment
     *
     * @param array $properties
     * @return void
     */
    public function updateProperties(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            } else {
                throw new \DomainException(sprintf('Comment does not have "%s" property.', $name));
            }
        }
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->author;
    }

    /**
     * @return int|null
     */
    public function getOwnerId()
    {
        return $this->getOwner() ? $this->getOwner()->getId() : null;
    }
}
