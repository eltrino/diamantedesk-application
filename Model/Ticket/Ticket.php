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
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Shared\DomainEventProvider;
use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\AttachmentWasAddedToTicket;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\AttachmentWasDeletedFromTicket;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\CommentWasAddedToTicket;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketAssigneeWasChanged;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketStatusWasChanged;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasCreated;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasDeleted;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUnassigned;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUpdated;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\User;

class Ticket extends DomainEventProvider implements Entity, AttachmentHolder
{
    const UNASSIGNED_LABEL = 'Unassigned';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var UniqueId
     */
    protected $uniqueId;

    /**
     * @var TicketSequenceNumber
     */
    protected $sequenceNumber;

    /**
     * @var TicketKey
     */
    protected $key;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var Priority
     */
    protected $priority;

    /**
     * @var Branch
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

    /**
     * @param UniqueId $uniqueId
     * @param TicketSequenceNumber $sequenceNumber
     * @param $subject
     * @param $description
     * @param Branch $branch
     * @param User $reporter
     * @param User|null $assignee
     * @param Source $source
     * @param Priority $priority
     * @param Status $status
     */
    public function __construct(
        UniqueId $uniqueId,
        TicketSequenceNumber $sequenceNumber,
        $subject, $description,
        Branch $branch,
        User $reporter,
        $assignee,
        Source $source,
        Priority $priority,
        Status $status
    ) {
        $this->uniqueId = $uniqueId;
        $this->sequenceNumber = $sequenceNumber;
        $this->subject = $subject;
        $this->description = $description;
        $this->branch = $branch;
        $this->status = $status;
        $this->priority = $priority;
        $this->reporter = $reporter;
        $this->assignee = $assignee;
        $this->comments  = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
        $this->source = $source;

        $assigneeEmail = null;
        if(!is_null($assignee)) {
            $assigneeEmail = $assignee->getEmail();
        }
        $this->raise(new TicketWasCreated($this->uniqueId, $branch->getName(), $subject, $description,
            $reporter->getEmail(), $assigneeEmail, $priority, $status, $source, $this->getRecipientsList()));
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return UniqueId
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return TicketSequenceNumber
     */
    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * @return TicketKey
     */
    public function getKey()
    {
        $this->initializeKey();
        return $this->key;
    }

    /**
     * Initialize TicketKey
     * @return void
     */
    private function initializeKey()
    {
        if ($this->sequenceNumber->getValue() && is_null($this->key)) {
            $this->key = new TicketKey($this->branch->getKey(), $this->sequenceNumber->getValue());
        }
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
        $this->raise(new CommentWasAddedToTicket($this->uniqueId, $this->subject, $this->getRecipientsList(),
            $comment->getContent()));
    }

    /** LEGACY CODE START */

    /**
     * @param $subject
     * @param $description
     * @param User $reporter
     * @param $priority
     * @param $status
     * @param $source
     */
    public function update($subject, $description, User $reporter, $priority, $status, $source)
    {
        $priority = new Priority($priority);
        $status   = new Status($status);
        $source   = new Source($source);

        if ($this->subject !== $subject || $this->description !== $description || $this->reporter !== $reporter
            || $this->priority->getValue() !== $priority->getValue() || $this->status->getValue() !== $status->getValue()
            || $this->source->getValue() !== $source->getValue()) {

            $this->raise(new TicketWasUpdated($this->uniqueId, $subject, $description, $reporter->getEmail(),
                $priority, $status, $source, $this->getRecipientsList()));
        }

        $this->subject     = $subject;
        $this->description = $description;
        $this->reporter    = $reporter;
        $this->status      = $status;
        $this->priority    = $priority;
        $this->source      = $source;
        $this->updatedAt   = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return array
     */
    public function getRecipientsList()
    {
        if ($this->getAssignee()) {
            $recipientsList = array(
                $this->getReporter()->getEmail(),
                $this->getAssignee()->getEmail(),
            );
        } else {
            $recipientsList = array(
                $this->getReporter()->getEmail()
            );
        }

        return $recipientsList;
    }

    /**
     * @param $status
     */
    public function updateStatus($status)
    {
        $status = new Status($status);

        if ($this->status->getValue() !== $status->getValue()) {
            $this->raise(new TicketStatusWasChanged($this->uniqueId, $this->subject,
                $status, $this->getRecipientsList()));
        }

        $this->status = $status;
    }

    /**
     * @param User $newAssignee
     */
    public function assign(User $newAssignee)
    {
        if (is_null($this->assignee) || $newAssignee->getId() != $this->assignee->getId()) {
            $this->assignee = $newAssignee;
            $this->raise(new TicketAssigneeWasChanged($this->uniqueId, $this->subject, $newAssignee->getEmail(),
                $this->getRecipientsList()));
        }
    }

    public function unassign()
    {
        $this->assignee = null;
        $this->raise(new TicketWasUnassigned($this->uniqueId, $this->subject, $this->getRecipientsList()));
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

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments->add($attachment);
        $this->raise(new AttachmentWasAddedToTicket($this->uniqueId, $this->subject, $attachment->getFilename(),
            $this->getRecipientsList()));
    }

    /**
     * @param Attachment $attachment
     */
    public function removeAttachment(Attachment $attachment)
    {
        $this->attachments->remove($attachment->getId());
        $this->raise(new AttachmentWasDeletedFromTicket($this->uniqueId, $this->subject, $attachment->getFilename(),
            $this->getRecipientsList()));
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
            /**
             * @var $elm Attachment
             */
            return $elm->getId() == $attachmentId;
        })->first();
        return $attachment;
    }

    /**
     * @return string
     */
    public function getUnassignedLabel()
    {
        return self::UNASSIGNED_LABEL;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    public function delete()
    {
        $this->raise(new TicketWasDeleted($this->uniqueId, $this->subject, $this->getRecipientsList()));
    }
}
