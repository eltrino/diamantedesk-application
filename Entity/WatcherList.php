<?php

namespace Diamante\DeskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * WatcherList
 *
 * @ORM\Table(name="diamante_watcher_list")
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Entity\WatcherListRepository")
 * @Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "permissions"="VIEW;CREATE;EDIT;DELETE",
 *              "group_name"="DiamanteDesk"
 *          }
 *      }
 * )
 */
class WatcherList extends \Diamante\DeskBundle\Model\Ticket\WatcherList
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Ticket
     *
     * @ORM\ManyToOne(targetEntity="Ticket", cascade={"persist"})
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ticket;

    /**
     * @var string
     *
     * @ORM\Column(name="user_type", type="string", length=255, nullable=false)
     */
    private $userType;

    public static function getClassName()
    {
        return __CLASS__;
    }
}
