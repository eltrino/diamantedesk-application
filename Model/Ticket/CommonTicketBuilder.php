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

use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class CommonTicketBuilder implements TicketBuilder
{
    /**
     * @var TicketFactory
     */
    private $factory;

    /**
     * @var Repository
     */
    private $branchRepository;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var UniqueId
     */
    private $uniqueId;

    /**
     * @var TicketSequenceNumber
     */
    private $sequenceNumber;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Branch
     */
    private $branch;

    /**
     * @var User
     */
    private $reporter;

    /**
     * @var OroUser
     */
    private $assignee;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var Priority
     */
    private $priority;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var ArrayCollection
     */
    private $tags;

    public function __construct(TicketFactory $factory, Repository $branchRepository, UserService $userService)
    {
        $this->factory = $factory;
        $this->branchRepository = $branchRepository;
        $this->userService = $userService;
    }

    /**
     * @param UniqueId $uniqueId
     * @return $this
     */
    public function setUniqueId(UniqueId $uniqueId)
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * @param TicketSequenceNumber $sequenceNumber
     * @return $this
     */
    public function setSequenceNumber(TicketSequenceNumber $sequenceNumber)
    {
        $this->sequenceNumber = $sequenceNumber;
        return $this;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = (string) $subject;
        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setBranchId($id)
    {
        $branch = $this->branchRepository->get((integer) $id);
        if (is_null($branch)) {
            throw new \LogicException('Branch loading failed, branch not found.');
        }
        $this->branch = $branch;
        return $this;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setReporter($id)
    {
        $reporter = User::fromString($id);
        $this->reporter = $reporter;
        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setAssigneeId($id)
    {
        if (!empty($id)) {
            $assignee = $this->userService->getByUser(new User((integer) $id, User::TYPE_ORO));
            $this->assignee = $assignee;
        }
        return $this;
    }

    /**
     * @param string $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = new Priority((string) $priority);
        return $this;
    }

    /**
     * @param string $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = new Source((string) $source);
        return $this;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = new Status((string) $status);
        return $this;
    }

    /**
     * @param array|ArrayCollection|null $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }


    /**
     * @return void
     */
    private function initializeDefaultValues()
    {
        if (is_null($this->uniqueId)) {
            $this->uniqueId = UniqueId::generate();
        }

        if (is_null($this->sequenceNumber)) {
            $this->sequenceNumber = new TicketSequenceNumber(null);
        }

        if (is_null($this->priority)) {
            $this->priority = new Priority(Priority::PRIORITY_MEDIUM);
        }

        if (is_null($this->source)) {
            $this->source = new Source(Source::PHONE);
        }

        if (is_null($this->status)) {
            $this->status = new Status(Status::NEW_ONE);
        }

        if (is_null($this->tags)) {
            $this->tags = new ArrayCollection();
        }
    }

    /**
     * Builds Ticket object and unset all previously defined values
     * @return Ticket
     */
    public function build()
    {
        $this->initializeDefaultValues();

        $ticket = $this
            ->factory
                ->create(
                    $this->uniqueId, $this->sequenceNumber, $this->subject, $this->description,
                    $this->branch, $this->reporter, $this->assignee,
                    $this->priority, $this->source, $this->status, $this->tags
                );

        $this->clearBuilderValues();

        return $ticket;
    }

    /**
     * @return void
     */
    private function clearBuilderValues()
    {
        $this->uniqueId = null;
        $this->sequenceNumber = null;
        $this->subject = null;
        $this->description = null;
        $this->branch = null;
        $this->reporter = null;
        $this->assignee = null;
        $this->priority = null;
        $this->source = null;
        $this->status = null;
        $this->tags = null;
    }
} 
