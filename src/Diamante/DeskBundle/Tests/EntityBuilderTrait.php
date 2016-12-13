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
namespace Diamante\DeskBundle\Tests;

use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\UserBundle\Model\User;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference;
use \Diamante\EmailProcessingBundle\Model\Message\MessageSender;
use \Diamante\UserBundle\Entity\DiamanteUser;

trait EntityBuilderTrait
{
    /**
     * @return Ticket
     */
    protected function getTicket()
    {
        $reporterId = 2;

        $status = Status::NEW_ONE;
        $priority = Priority::PRIORITY_LOW;
        $source = Source::PHONE;
        $number = new TicketSequenceNumber(null);
        $reporter = $this->createReporter($reporterId);

        $ticket = new Ticket(
            UniqueId::generate(),
            $number,
            'Subject',
            'Description',
            $this->createBranch(),
            $reporter,
            $this->createAssignee(),
            new Source($source),
            new Priority($priority),
            new Status($status)
        );

        return $ticket;
    }

    /**
     * @return Branch
     */
    protected function createBranch()
    {
        return new Branch('DUMM', 'DUMMY_NAME', 'DUMYY_DESC');
    }

    protected function getMessageReference()
    {
        return new MessageReference('message_id', $this->getTicket());
    }

    /**
     * @param int $id
     * @return User
     */
    protected function createReporter($id = 1)
    {
        return new User($id, User::TYPE_DIAMANTE);
    }

    /**
     * @return OroUser
     */
    protected function createAssignee()
    {
        return $this->createOroUser();
    }

    /**
     * @return OroUser
     */
    protected function createOroUser()
    {
        return new OroUser();
    }

    protected function createDiamanteUser()
    {
        return new DiamanteUser('dummy@mail.com', 'dummy_name', 'dummy_surname');
    }

    /**
     * @return MessageSender
     */
    private function getDummyFrom()
    {
        return new MessageSender('from@gmail.com', 'Dummy User');
    }
}