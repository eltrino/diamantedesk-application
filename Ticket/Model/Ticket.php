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

namespace Eltrino\DiamanteDeskBundle\Ticket\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Eltrino\DiamanteDeskBundle\Attachment\Model\Attachment;
use Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentHolder;
use Oro\Bundle\UserBundle\Entity\User;

class Ticket implements AttachmentHolder
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Model\Status
     */
    protected $status;

    /**
     * @var integer
     */
    protected $priority;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\Branch
     */
    protected $branch;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\User
     */
    protected $reporter;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\User
     */
    protected $assignee;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $comments;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
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

    public function __construct($subject, $description, $branch, $reporter, $assignee, $status = null)
    {
        $this->subject = $subject;
        $this->description = $description;
        $this->branch = $branch;

        if (null == $status) {
            $status = Status::NEW_ONE;
        }

        $this->status = new Status($status);
        $this->priority = new Priority();
        $this->reporter = $reporter;
        $this->assignee = $assignee;
        $this->comments  = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @return string
     */
    public function getBranchName()
    {
        return $this->branch->getName();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return mixed
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @return string
     */
    public function getReporterId()
    {
        return $this->reporter->getId();
    }

    /**
     * @return string
     */
    public function getReporterFullName()
    {
        return $this->reporter->getFirstName() . ' ' . $this->reporter->getLastName();
    }

    public function getAssignee()
    {
        return $this->assignee;
    }

    public function getAssigneeId()
    {
        return $this->assignee->getId();
    }

    /**
     * @return string
     */
    public function getAssigneeFullName()
    {
        return $this->assignee->getFirstName() . ' ' . $this->assignee->getLastName();
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

    public function postNewComment(Comment $comment)
    {
        $this->comments->add($comment);
    }

    /** LEGACY CODE START */

    public function update($subject, $description, User $reporter, $status)
    {
        $this->subject = $subject;
        $this->description = $description;
        $this->reporter = $reporter;
        $this->status = new Status($status);
    }

    public function updateStatus($status)
    {
        $this->status = new Status($status);
    }

    public function assign(User $newAssignee)
    {
        $this->assignee = $newAssignee;
    }

    /** LEGACY CODE END */

    /**
     * Retrieves ticket comments
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    public function addAttachment(Attachment $attachment)
    {
        $this->attachments->add($attachment);
    }

    public function removeAttachment(Attachment $attachment)
    {
        $this->attachments->remove($attachment->getId());
    }

    /**
     * Returns unmodifiable collection
     * @return ArrayCollection
     */
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
}
