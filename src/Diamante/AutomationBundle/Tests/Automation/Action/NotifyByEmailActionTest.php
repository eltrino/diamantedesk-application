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

use Diamante\AutomationBundle\Automation\Action\Email\NotifyByEmailAction;
use Diamante\AutomationBundle\EventListener\WorkflowListener;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\AutomationBundle\Rule\Fact\Fact;
use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotifyByEmailActionTest extends \PHPUnit_Framework_TestCase
{
    const SUBJECT      = 'Subject';
    const DESCRIPTION  = 'Description';

    /**
     * @var /Diamante\DeskBundle\Infrastructure\Notification\NotificationManager
     * @Mock Diamante\DeskBundle\Infrastructure\Notification\NotificationManager
     */
    private $notificationManager;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     * @Mock Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Diamante\UserBundle\Api\Internal\UserServiceImpl
     * @Mock Diamante\UserBundle\Api\Internal\UserServiceImpl
     */
    private $userService;

    /**
     * @var \Diamante\AutomationBundle\Infrastructure\Resolver\EmailResolver
     * @Mock Diamante\AutomationBundle\Infrastructure\Resolver\EmailResolver
     */
    private $emailResolver;

    /**
     * @var NotifyByEmailAction
     */
    private $service;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new NotifyByEmailAction($this->notificationManager, $this->container);
    }

    /**
     * @test
     */
    public function testExecute()
    {
        $ticket = [
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
        $fact = new Fact($ticket, 'ticket', WorkflowListener::CREATED, $this->getChangeset());
        $context = new ExecutionContext(['status' => Status::NEW_ONE, 'priority' => Priority::PRIORITY_HIGH]);
        $context->setFact($fact);
        $this->service->updateContext($context);

        $this->container
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('diamante.automation.email.resolver'))
            ->will($this->returnValue($this->emailResolver));

        $this->emailResolver
            ->expects($this->once())
            ->method('getList')
            ->will($this->returnValue(['dummy-email1@diamantedesk.com', 'dummy-email2@diamantedesk.com']));

        $this->container
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('diamante.user.service'))
            ->will($this->returnValue($this->userService));

        $this->container
            ->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('diamante.user.service'))
            ->will($this->returnValue($this->userService));

        $this->userService
            ->expects($this->any())
            ->method('getUserInstanceByEmail')
            ->will($this->returnValue($this->createOroUser()));

        $this->notificationManager
            ->expects($this->any())
            ->method('notifyByScenario');

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
     * @return array
     */
    private function getChangeset()
    {
        $ticketChangeset = [
            'uniqueId' => [null, new UniqueId('unique_id')],
            'sequenceNumber' => [null, new TicketSequenceNumber(13)],
            'subject' => [null, self::SUBJECT],
            'description' => [null, self::DESCRIPTION],
            'branch' => [null, $this->createBranch()],
            'reporter' => [null, new User(1, User::TYPE_DIAMANTE)],
            'assignee' => [null, $this->createAssignee()],
            'source' => [null, new Source(Source::PHONE)],
            'priority' => [null, new Priority(Priority::PRIORITY_LOW)],
            'status' => [null, new Status(Status::CLOSED)]
        ];

        return $ticketChangeset;
    }
}