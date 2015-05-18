<?php

namespace Diamante\DeskBundle\Model\Ticket;

use Diamante\DeskBundle\Model\Shared\Entity;

class WatcherList implements Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @var string
     */
    protected $userType;


    public function __construct($ticket, $userType)
    {
        $this->ticket = $ticket;
        $this->userType = $userType;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ticket
     *
     * @param Ticket $ticket
     * @return WatcherList
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return \stdClass
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set userTypes
     *
     * @param string $userType
     * @return WatcherList
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;

        return $this;
    }

    /**
     * Get userType
     *
     * @return string
     */
    public function getUserType()
    {
        return $this->userType;
    }

}