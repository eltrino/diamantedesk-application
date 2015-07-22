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

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository")
 * @ORM\Table(name="diamante_ticket_timeline")
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
class TicketTimeline extends \Diamante\DeskBundle\Model\Ticket\TicketTimeline
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="new", type="integer")
     */
    protected $new = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="solved", type="integer")
     */
    protected $solved = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="closed", type="integer")
     */
    protected $closed = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="reopen", type="integer")
     */
    protected $reopen = 0;

    public static function getClassName()
    {
        return __CLASS__;
    }
}
