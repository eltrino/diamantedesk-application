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
namespace Diamante\DeskBundle\Entity;

use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Diamante\UserBundle\Model\User as UserModel;

/**
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Infrastructure\Persistence\DoctrineTicketRepository")
 * @ORM\Table(name="diamante_ticket")
 * @ORM\EntityListeners({"Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\TicketListener"})
 * @Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "permissions"="VIEW;CREATE;EDIT;DELETE",
 *              "group_name"="DiamanteDesk"
 *          },
 *         "dataaudit"={"auditable"=true}
 *      }
 * )
 */
class Ticket extends \Diamante\DeskBundle\Model\Ticket\Ticket
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="unique_id", type="ticket_unique_id", nullable=false)
     */
    protected $uniqueId;

    /**
     * @var TicketSequenceNumber
     *
     * @ORM\Column(name="number", type="ticket_sequence_number")
     */
    protected $sequenceNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="status")
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="priority")
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $priority;

    /**
     * @var Branch
     *
     * @ORM\ManyToOne(targetEntity="Branch")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $branch;

    /**
     * @var UserModel
     * @ORM\Column(type="user_type", name="reporter_id")
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $reporter;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assignee_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $assignee;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="ticket", cascade={"remove" ,"persist"})
     */
    protected $comments;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Attachment")
     * @ORM\JoinTable(name="diamante_ticket_attachments",
     *      joinColumns={@ORM\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="attachment_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $attachments;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="WatcherList", mappedBy="ticket", cascade={"persist", "remove"})
     */
    protected $watcherList;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="source")
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $source;

    /**
     * @var ArrayCollection
     */
    protected $tags;

    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('[%s] %s', $this->getKey() ? $this->getKey() : 'moved', $this->getSubject());
    }
}
