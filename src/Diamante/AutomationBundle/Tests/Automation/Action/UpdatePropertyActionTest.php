<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Tests\Automation\Action;

use Diamante\AutomationBundle\Automation\Action\UpdatePropertyAction;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\AutomationBundle\Rule\Fact\Fact;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Diamante\DeskBundle\Entity\Branch;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class UpdatePropertyActionTest extends \PHPUnit_Framework_TestCase
{
    const SUBJECT      = 'Subject';
    const DESCRIPTION  = 'Description';

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     * @Mock Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $registry;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var UpdatePropertyAction
     */
    private $service;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new UpdatePropertyAction($this->registry);
    }

    /**
     * @test
     */
    public function testExecuteWithUpdatePropertiesMethod()
    {
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(13),
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            new User(1, User::TYPE_DIAMANTE),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::CLOSED)
        );
        $fact = new Fact($ticket, 'ticket');
        $context = new ExecutionContext(['status' => Status::NEW_ONE, 'priority' => Priority::PRIORITY_HIGH]);
        $context->setFact($fact);
        $this->service->updateContext($context);

        $this->registry
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($ticket);

        $this->service->execute();
    }

    /**
     * @test
     */
    public function testExecuteWithoutUpdatePropertiesMethod()
    {
        $user = $this->createOroUser();
        $fact = new Fact($user, 'oroUser');
        $context = new ExecutionContext(['firstName' => 'Mike', 'lastName' => 'Bot']);
        $context->setFact($fact);
        $this->service->updateContext($context);

        $this->registry
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->service->execute();
    }

    /**
     * @return Branch
     */
    private function createBranch()
    {
        return new Branch('DUMM', 'DUMMY_NAME', 'DUMYY_DESC');
    }

    /**
     * @return OroUser
     */
    private function createAssignee()
    {
        return $this->createOroUser();
    }

    /**
     * @return OroUser
     */
    private function createOroUser()
    {
        return new OroUser();
    }
}