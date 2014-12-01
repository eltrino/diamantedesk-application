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
namespace Diamante\DiamanteDeskBundle\Tests\EventListener\Mail;

use Diamante\DeskBundle\EventListener\Mail\TicketProcessManager;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class TicketProcessManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TicketProcessManager
     */
    private $ticketProcessManager;

    /**
     * @var \Twig_Environment
     * @Mock \Twig_Environment
     */
    private $twig;

    /**
     * @var \Swift_Mailer
     * @Mock \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var \Swift_Message
     * @Mock \Swift_Message
     */
    private $message;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     * @Mock \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUpdated
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUpdated
     */
    private $ticketWasUpdatedEvent;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasCreated
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasCreated
     */
    private $ticketWasCreatedEvent;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Events\AttachmentWasAddedToTicket
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Events\AttachmentWasAddedToTicket
     */
    private $attachmentWasAddedToTicketEvent;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketStatusWasChanged
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketStatusWasChanged
     */
    private $ticketStatusWasChangedEvent;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketAssigneeWasChanged
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketAssigneeWasChanged
     */
    private $ticketAssigneeWasChangedEvent;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUnassigned
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUnassigned
     */
    private $ticketWasUnassignedEvent;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\User
     * @Mock \Oro\Bundle\UserBundle\Entity\User
     */
    private $user;

    /**
     * @var array
     */
    private $recipientsList = array(
        'no-reply.reporter@example.com',
        'no-reply.assignee@example.com',
    );

    /**
     * @var \Oro\Bundle\ConfigBundle\Config\ConfigManager
     * @Mock \Oro\Bundle\ConfigBundle\Config\ConfigManager
     */
    private $configManager;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->senderEmail = 'no-reply@example.com';

        $this->ticketProcessManager = new TicketProcessManager(
            $this->twig,
            $this->mailer,
            $this->securityFacade,
            $this->configManager,
            $this->senderEmail
        );
    }

    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->ticketProcessManager);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(
                'ticketWasUpdated'           => 'onTicketWasUpdated',
                'ticketWasCreated'           => 'onTicketWasCreated',
                'attachmentWasAddedToTicket' => 'onAttachmentWasAddedToTicket',
                'ticketStatusWasChanged'     => 'onTicketStatusWasChanged',
                'ticketAssigneeWasChanged'   => 'onTicketAssigneeWasChanged',
                'ticketWasUnassigned'        => 'onTicketWasUnassigned',
            ),
            $this->ticketProcessManager->getSubscribedEvents()
        );
    }

    public function testOnTicketWasUpdated()
    {
        $this->ticketWasUpdatedEvent
            ->expects($this->once())
            ->method('getEventName');

        $this->ticketWasUpdatedEvent
            ->expects($this->any())
            ->method('getRecipientsList')
            ->will($this->returnValue($this->recipientsList));

        $this->ticketWasUpdatedEvent
            ->expects($this->atLeastOnce())
            ->method('getSubject')
            ->will($this->returnValue('Subject'));

        $this->ticketWasUpdatedEvent
            ->expects($this->once())
            ->method('getDescription')
            ->will($this->returnValue('Description'));

        $this->ticketWasUpdatedEvent
            ->expects($this->once())
            ->method('getReporterEmail')
            ->will($this->returnValue('no-reply.reporter@example.com'));

        $this->ticketWasUpdatedEvent
            ->expects($this->once())
            ->method('getPriority')
            ->will($this->returnValue(new Priority(Priority::PRIORITY_LOW)));

        $this->ticketWasUpdatedEvent
            ->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(new Status(Status::OPEN)));

        $this->ticketWasUpdatedEvent
            ->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue(new Source(Source::PHONE)));

        $this->ticketProcessManager->onTicketWasUpdated($this->ticketWasUpdatedEvent);
    }

    public function testOnTicketWasCreated()
    {
        $this->ticketWasCreatedEvent
            ->expects($this->once())
            ->method('getEventName');

        $this->ticketWasCreatedEvent
            ->expects($this->atLeastOnce())
            ->method('getBranchName')
            ->will($this->returnValue('BranchName'));

        $this->ticketWasCreatedEvent
            ->expects($this->atLeastOnce())
            ->method('getSubject')
            ->will($this->returnValue('Subject'));

        $this->ticketWasCreatedEvent
            ->expects($this->any())
            ->method('getRecipientsList')
            ->will($this->returnValue($this->recipientsList));

        $this->ticketWasCreatedEvent
            ->expects($this->once())
            ->method('getDescription')
            ->will($this->returnValue('Description'));

        $this->ticketWasCreatedEvent
            ->expects($this->once())
            ->method('getReporterEmail')
            ->will($this->returnValue('no-reply.reporter@example.com'));

        $this->ticketWasCreatedEvent
            ->expects($this->once())
            ->method('getAssigneeEmail')
            ->will($this->returnValue('no-reply.assignee@example.com'));

        $this->ticketWasCreatedEvent
            ->expects($this->once())
            ->method('getPriority')
            ->will($this->returnValue(new Priority(Priority::PRIORITY_LOW)));

        $this->ticketWasCreatedEvent
            ->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(new Status(Status::OPEN)));

        $this->ticketWasCreatedEvent
            ->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue(new Source(Source::PHONE)));

        $this->ticketProcessManager->onTicketWasCreated($this->ticketWasCreatedEvent);
    }

    public function testOnAttachmentWasAddedToTicket()
    {
        $this->attachmentWasAddedToTicketEvent
            ->expects($this->atLeastOnce())
            ->method('getEventName');

        $this->attachmentWasAddedToTicketEvent
            ->expects($this->any())
            ->method('getRecipientsList')
            ->will($this->returnValue($this->recipientsList));

        $this->attachmentWasAddedToTicketEvent
            ->expects($this->once())
            ->method('getAttachmentName')
            ->will($this->returnValue('attachmentName'));

        $this->ticketProcessManager->onAttachmentWasAddedToTicket($this->attachmentWasAddedToTicketEvent);
    }

    public function testOnTicketStatusWasChanged()
    {
        $this->ticketStatusWasChangedEvent
            ->expects($this->atLeastOnce())
            ->method('getEventName');

        $this->ticketStatusWasChangedEvent
            ->expects($this->any())
            ->method('getRecipientsList')
            ->will($this->returnValue($this->recipientsList));

        $this->ticketStatusWasChangedEvent
            ->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(new Status(Status::OPEN)));

        $this->ticketProcessManager->onTicketStatusWasChanged($this->ticketStatusWasChangedEvent);
    }

    public function testOnTicketAssigneeWasChanged()
    {
        $this->ticketAssigneeWasChangedEvent
            ->expects($this->atLeastOnce())
            ->method('getEventName');

        $this->ticketAssigneeWasChangedEvent
            ->expects($this->any())
            ->method('getRecipientsList')
            ->will($this->returnValue($this->recipientsList));

        $this->ticketAssigneeWasChangedEvent
            ->expects($this->once())
            ->method('getAssigneeEmail')
            ->will($this->returnValue('no-reply.assignee@example.com'));

        $this->ticketProcessManager->onTicketAssigneeWasChanged($this->ticketAssigneeWasChangedEvent);
    }

    public function testOnTicketWasUnassigned()
    {
        $this->ticketWasUnassignedEvent
            ->expects($this->atLeastOnce())
            ->method('getEventName');

        $this->ticketWasUnassignedEvent
            ->expects($this->any())
            ->method('getRecipientsList')
            ->will($this->returnValue($this->recipientsList));

        $this->ticketProcessManager->onTicketWasUnassigned($this->ticketWasUnassignedEvent);
    }

    public function testProcess()
    {
        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with('diamante_desk.email_notification')
            ->will($this->returnValue(true));

        $this->securityFacade
            ->expects($this->exactly(2))
            ->method('getLoggedUser')
            ->will($this->returnValue($this->user));

        $this->user
            ->expects($this->exactly(2))
            ->method('getFirstName')
            ->will($this->returnValue('firstName'));

        $this->user
            ->expects($this->exactly(2))
            ->method('getLastName')
            ->will($this->returnValue('lastName'));

        $userFullName = 'firstName' . ' ' . 'lastName';

        $options = array(
            'changes'     => array(),
            'attachments' => array(),
            'user'        => $userFullName,
            'header'      => null
        );

        $templates = array(
            'txt'  => 'DiamanteDeskBundle:Ticket/notification/mails/update:notification.txt.twig',
            'html' => 'DiamanteDeskBundle:Ticket/notification/mails/update:notification.html.twig'
        );

        $this->twig
            ->expects($this->exactly(2))
            ->method('render')
            ->will(
                $this->returnValueMap(
                    array(
                        array($templates['txt'], $options, 'test'),
                        array($templates['html'], $options, '<p>test</p>')
                    )
                )
            );

        $this->mailer
            ->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($this->message));

        $this->message->expects($this->once())
            ->method('setSubject');

        $this->message->expects($this->once())
            ->method('setFrom')
            ->with($this->senderEmail, $userFullName);

        $this->message->expects($this->once())
            ->method('setTo')
            ->with($this->recipientsList);

        $this->message->expects($this->once())
            ->method('setBody')
            ->with('test', 'text/plain');

        $this->message->expects($this->once())
            ->method('addPart')
            ->with('<p>test</p>', 'text/html');

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->message);

        $this->ticketProcessManager->setRecipientsList($this->recipientsList);
        $this->ticketProcessManager->process();
    }
}