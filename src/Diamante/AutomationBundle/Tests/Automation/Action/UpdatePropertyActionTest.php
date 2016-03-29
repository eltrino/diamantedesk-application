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
use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
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
     * @var \Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider
     * @Mock \Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var \Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag
     * @Mock \Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag
     */
    private $parameterBag;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\TicketRepository
     * @Mock \Diamante\DeskBundle\Model\Ticket\TicketRepository
     */
    private $ticketRepository;

    /**
     * @var UpdatePropertyAction
     */
    private $service;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new UpdatePropertyAction($this->registry, $this->configurationProvider);
    }

    /**
     * @test
     */
    public function testExecuteWithUpdatePropertiesMethod()
    {
        $target = [
            'id' => 1,
            'uniqueId' => new UniqueId('unique_id'),
            'sequenceNumber' => new TicketSequenceNumber(13),
            'subject' => self::SUBJECT,
            'description' => self::DESCRIPTION,
            'branch' => $this->createBranch(),
            'reporter' => new User(1, User::TYPE_DIAMANTE),
            'assignee' => $this->createAssignee(),
            'source' => new Source(Source::PHONE),
            'priority' => new Priority(Priority::PRIORITY_LOW),
            'status' => new Status(Status::CLOSED),
        ];
        $entity = $this->getTicket();
        $fact = new Fact($target, 'ticket');
        $context = new ExecutionContext(['status' => Status::NEW_ONE, 'priority' => Priority::PRIORITY_HIGH]);
        $context->setFact($fact);
        $this->service->updateContext($context);

        $this->configurationProvider
            ->expects($this->any())
            ->method('getEntityConfiguration')
            ->with($this->equalTo('ticket'))
            ->will($this->returnValue($this->parameterBag));

        $this->parameterBag
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('class'))
            ->will($this->returnValue('Diamante\DeskBundle\Entity\Ticket'));

        $this->registry
            ->expects($this->at(0))
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->ticketRepository));

        $this->ticketRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(1))
            ->will($this->returnValue($entity));

        $this->registry
            ->expects($this->at(1))
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($entity);

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

    /**
     * @return Ticket
     */
    private function getTicket()
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

        return $ticket;
    }
}