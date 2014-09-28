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
namespace Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing;

use Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket;
use Eltrino\DiamanteDeskBundle\Model\Shared\Entity;

class MessageReference implements Entity
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @param $messageId
     * @param $ticket
     */
    public function __construct($messageId, Ticket $ticket)
    {
        $this->messageId = $messageId;
        $this->ticket  = $ticket;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}
