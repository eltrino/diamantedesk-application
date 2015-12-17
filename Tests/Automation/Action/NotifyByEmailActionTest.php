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

use Diamante\AutomationBundle\Automation\Action\NotifyByEmailAction;
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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

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

        $this->container
            ->expects($this->any())
            ->method('get')
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
}