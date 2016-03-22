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
use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Shared\Owned;
use Diamante\DeskBundle\Model\Shared\Updatable;
use Diamante\UserBundle\Model\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class Ticket implements Entity, AttachmentHolder, Taggable, Updatable, Owned
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
     * @var User
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
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $watcherList;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $statusUpdatedSince;

    /**
     * @var \DateTime
     */
    protected $assignedSince;

    /**
     * @var ArrayCollection
     */
    protected $tags;

    /**
     * @param UniqueId $uniqueId
     * @param TicketSequenceNumber $sequenceNumber
     * @param $subject
     * @param $description
     * @param Branch $branch
     * @param User $reporter
     * @param OroUser|null $assignee
     * @param Source $source
     * @param Priority $priority
     * @param Status $status
     * @param ArrayCollection|null $tags
     */
    public function __construct(
        UniqueId $uniqueId,
        TicketSequenceNumber $sequenceNumber,
        $subject,
        $description,
        Branch $branch,
        User $reporter,
        OroUser $assignee = null,
        Source $source,
        Priority $priority,
        Status $status,
        $tags = null
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
        $this->watcherList = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
        $this->statusUpdatedSince = clone $this->createdAt;
        $this->assignedSince = clone $this->createdAt;
        $this->source = $source;
        $this->tags = is_null($tags) ? new ArrayCollection() : $tags;
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
     * @return int
     */
    public function getBranchId()
    {
        return $this->branch->getId();
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
     * @return string
     */
    public function getStatusValue()
    {
        return $this->status->getValue();
    }

    /**
     * @return Priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getPriorityValue()
    {
        return $this->priority->getValue();
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
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getAssignee()
    {
        return $this->assignee;
    }

    /**
     * @return int
     */
    public function getAssigneeId()
    {
        if (is_null($this->assignee)) {
            return null;
        }
        return $this->assignee->getId();
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
    public function getStatusUpdatedSince()
    {
        return $this->statusUpdatedSince;
    }

    /**
     * @return \DateTime
     */
    public function getAssignedSince()
    {
        return $this->assignedSince;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return null|string
     */
    public function getAssigneeFullName()
    {
        if (!empty($this->assignee)) {
            return $this->assignee->getFirstName() . ' ' . $this->assignee->getLastName();
        }

        return null;
    }

    /**
     * @return string
     */
    public function getReporterFullName()
    {
        return 'Reporter';
    }

    /**
     * Stub logo method
     * @return null
     */
    public function getLogo()
    {
        return null;
    }

    /**
     * Returns the unique taggable resource identifier
     *
     * @return string
     */
    public function getTaggableId()
    {
        return $this->id;
    }

    /**
     * Returns the collection of tags for this Taggable entity
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();
        return $this->tags;
    }

    /**
     * Set tag collection
     *
     * @param $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function postNewComment(Comment $comment)
    {
        $this->comments->add($comment);
    }

    /** LEGACY CODE START */

    /**
     * @param string $subject
     * @param string $description
     * @param User $reporter
     * @param Priority $priority
     * @param Status $status
     * @param Source $source
     * @param OroUser|null $assignee
     */
    public function update(
        $subject,
        $description,
        User $reporter,
        Priority $priority,
        Status $status,
        Source $source,
        OroUser $assignee = null,
        $tags
    ) {
        $this->subject     = $subject;
        $this->description = $description;
        $this->reporter    = $reporter;
        $this->priority    = $priority;
        $this->source      = $source;
        $this->updatedAt   = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->tags        = $tags;

        $this->processUpdateStatus($status);

        if (is_null($assignee)) {
            $this->processUnAssign();
        } else {
            $this->processAssign($assignee);
        }
    }

    /**
     * Update ticket status
     * @param Status $status
     * @return void
     */
    public function updateStatus(Status $status)
    {
        if ($this->status->notEquals($status)) {
            $this->processUpdateStatus($status);
        }
    }

    /**
     * @param Status $status
     */
    private function processUpdateStatus(Status $status)
    {
        $this->status = $status;
    }

    /**
     * @param Branch $branch
     * @param TicketSequenceNumber|null $sequenceNumber
     */
    public function move(Branch $branch, TicketSequenceNumber $sequenceNumber = null)
    {
        if ($sequenceNumber == null) {
            $sequenceNumber = new TicketSequenceNumber();
        }
        $this->branch = $branch;
        $this->sequenceNumber = $sequenceNumber;
        $this->key = null;
    }

    /**
     * Assign new assignee (User) to ticket
     * @param OroUser $newAssignee
     * @return void
     */
    public function assign(OroUser $newAssignee)
    {
        if (is_null($this->assignee) || $newAssignee->getId() != $this->assignee->getId()) {
            $this->processAssign($newAssignee);
        }
    }

    /**
     * @param OroUser $newAssignee
     * @return void
     */
    private function processAssign(OroUser $newAssignee)
    {
        $this->assignee = $newAssignee;
    }

    /**
     * Un assign ticket
     * @return void
     */
    public function unAssign()
    {
        $this->processUnAssign();
    }

    /**
     * @return void
     */
    private function processUnAssign()
    {
        $this->assignee = null;
    }

    /**
     * Retrieves ticket comments
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Retrieves ticket watchers
     * @return ArrayCollection
     */
    public function getWatcherList()
    {
        return $this->watcherList;
    }

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments->add($attachment);
    }

    /**
     * @param Attachment $attachment
     */
    public function removeAttachment(Attachment $attachment)
    {
        $this->attachments->removeElement($attachment);
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

    /**
     * @return string
     */
    public function getSourceValue()
    {
        return $this->source->getValue();
    }

    /**
     * Update properties of the ticket
     *
     * @param array $properties
     * @return void
     */
    public function updateProperties(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) {
                throw new \DomainException(sprintf('Ticket does not have "%s" property.', $name));
            }

            if (in_array(strtolower($name), ['status', 'priority', 'source'])) {
                $propertyClass = __NAMESPACE__ . '\\' . ucfirst($name);
                $value = new $propertyClass($value);
            }

            if ($this->$name !== $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->reporter;
    }

    /**
     * @return int|null
     */
    public function getOwnerId()
    {
        return $this->getOwner() ? $this->getOwner()->getId() : null;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PreUpdate
     *
     * @param PreUpdateEventArgs $event
     */
    public function updatePropertiesTimestamp(PreUpdateEventArgs $event)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        if ($event->hasChangedField('status')) {
            $newStatusValue = $event->getNewValue('status')->getValue();
            $oldStatusValue = $event->getOldValue('status')->getValue();

            if ($newStatusValue != $oldStatusValue) {
                $this->statusUpdatedSince = $now;
            }
        }

        if ($event->hasChangedField('assignee')) {
            $this->assignedSince = $now;
        }
    }
}
