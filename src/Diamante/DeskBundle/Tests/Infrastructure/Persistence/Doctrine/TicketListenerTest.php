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
namespace Diamante\DeskBundle\Tests\Infrastructure\Persistence\Doctrine;

use Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\TicketListener;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\DeskBundle\Tests\EntityBuilderTrait;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class TicketListenerTest extends \PHPUnit_Framework_TestCase
{
    use EntityBuilderTrait;

    /**
     * @var TicketListener
     */
    private $listener;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     * @Mock \Doctrine\ORM\EntityManagerInterface
     */
    private $objectManager;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     * @Mock \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Diamante\UserBundle\Api\UserService
     * @Mock \Diamante\UserBundle\Api\UserService
     */
    private $diamanteUserService;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->listener = new TicketListener($this->container);
    }

    public function testPrePersistHandler()
    {
        $branchId = 1;
        $ticketSequenceNumberValue = 9;
        $branch = new BranchStub('DB', 'Dummy Branch', 'Desc');
        $branch->setSequenceNumber(10);
        $branch->setId($branchId);
        $reporter = new User(1, User::TYPE_DIAMANTE);

        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(),
            'Subject',
            'Description',
            $branch,
            $reporter,
            new OroUser(),
            new Source(Source::WEB),
            new Priority(Priority::PRIORITY_MEDIUM),
            new Status(Status::NEW_ONE)
        );
        $event = new LifecycleEventArgs($ticket, $this->objectManager);

        $this->container->expects($this->once())
            ->method('get')
            ->with('diamante.user.service')
            ->will($this->returnValue($this->diamanteUserService));

        $this->diamanteUserService->expects($this->once())
            ->method('getByUser')
            ->will($this->returnValue($this->createDiamanteUser()));

        $this->listener->prePersistHandler($ticket, $event);

        $this->assertEquals($ticketSequenceNumberValue+1, $ticket->getSequenceNumber()->getValue());
    }
} 
