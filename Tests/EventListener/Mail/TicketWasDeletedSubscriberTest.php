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
namespace Diamante\DeskBundle\Tests\EventListener\Mail;

use Diamante\DeskBundle\EventListener\Mail\TicketWasDeletedSubscriber;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Model\User\User as DiamanteUser;

class TicketWasDeletedSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TicketWasDeletedSubscriber
     */
    private $ticketWasDeletedSubscriber;

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
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasDeleted
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasDeleted
     */
    private $ticketWasDeletedEvent;

    /**
     * @var array
     */
    private $recipientsList;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\User
     * @Mock \Oro\Bundle\UserBundle\Entity\User
     */
    private $user;

    /**
     * @var \Oro\Bundle\ConfigBundle\Config\ConfigManager
     * @Mock \Oro\Bundle\ConfigBundle\Config\ConfigManager
     */
    private $configManager;

    /**
     * @var \Diamante\DeskBundle\Model\User\UserDetailsService
     * @Mock Diamante\DeskBundle\Model\User\UserDetailsService
     */
    private $userDetailsService;

    /**
     * @var \Diamante\DeskBundle\Model\User\UserDetails
     * @Mock Diamante\DeskBundle\Model\User\UserDetails
     */
    private $userDetails;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\TicketRepository
     * @Mock Diamante\DeskBundle\Model\Ticket\TicketRepository
     */
    private $ticketRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Ticket
     * @Mock Diamante\DeskBundle\Model\Ticket\Ticket
     */
    private $ticket;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\UniqueId
     * @Mock Diamante\DeskBundle\Model\Ticket\UniqueId
     */
    private $uniqueId;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\TicketKey
     * @Mock Diamante\DeskBundle\Model\Ticket\TicketKey
     */
    private $ticketKey;

    /**
     * @var string
     */
    private $senderHost;

    /**
     * @var string
     */
    private $ticketKeyValue;

    /**
     * @var Swift_Mime_HeaderSet
     * @Mock Swift_Mime_HeaderSet
     */
    private $headers;


    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->senderEmail = 'no-reply@example.com';
        $this->senderHost     = 'sender@example.com';
        $this->ticketKeyValue = 'some_value';

        $this->recipientsList = array(
            new DiamanteUser(1, DiamanteUser::TYPE_DIAMANTE),
            new DiamanteUser(1, DiamanteUser::TYPE_ORO),
        );

        $this->ticketWasDeletedSubscriber = new TicketWasDeletedSubscriber(
            $this->twig,
            $this->mailer,
            $this->securityFacade,
            $this->configManager,
            $this->ticketRepository,
            $this->userDetailsService,
            $this->senderEmail,
            $this->senderHost
        );
    }

    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->ticketWasDeletedSubscriber);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(
                'ticketWasDeleted' => 'onTicketWasDeleted',
            ),
            $this->ticketWasDeletedSubscriber->getSubscribedEvents()
        );
    }

    public function testOnTicketWasDeleted()
    {
        $this->ticketWasDeletedEvent
            ->expects($this->atLeastOnce())
            ->method('getAggregateId')
            ->will($this->returnValue($this->uniqueId));

        $this->ticketWasDeletedEvent
            ->expects($this->atLeastOnce())
            ->method('getSubject')
            ->will($this->returnValue('Subject'));

        $this->ticketWasDeletedEvent
            ->expects($this->any())
            ->method('getRecipientsList')
            ->will($this->returnValue($this->recipientsList));

        $this->securityFacade
            ->expects($this->exactly(2))
            ->method('getLoggedUser')
            ->will($this->returnValue($this->user));

        $this->userDetailsService
            ->expects($this->any(0))
            ->method('fetch')
            ->will($this->returnValue($this->userDetails));

        $this->userDetails
            ->expects($this->at(1))
            ->method('getEmail')
            ->will($this->returnValue('no-reply.reporter@example.com'));

        $this->userDetails
            ->expects($this->at(2))
            ->method('getEmail')
            ->will($this->returnValue('no-reply.assignee@example.com'));

        $this->userDetails
            ->expects($this->any())
            ->method('getFullName')
            ->will($this->returnValue('FistName LastName'));

        $userFullName = $this->userDetails->getFullName();

        $options = array(
            'user'        => $userFullName,
            'header'      => 'Ticket was deleted'
        );

        $templates = array(
            'txt'  => 'DiamanteDeskBundle:Ticket/notification/mails/delete:notification.txt.twig',
            'html' => 'DiamanteDeskBundle:Ticket/notification/mails/delete:notification.html.twig'
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
            ->with(array(
                'no-reply.reporter@example.com',
                'no-reply.assignee@example.com',
            ));

        $this->message->expects($this->once())
            ->method('setBody')
            ->with('test', 'text/plain');

        $this->message->expects($this->once())
            ->method('addPart')
            ->with('<p>test</p>', 'text/html');

        $this->ticketRepository
            ->expects($this->once())
            ->method('getByUniqueId')
            ->with($this->equalTo($this->uniqueId))
            ->will($this->returnValue($this->ticket));

        $this->ticket
            ->expects($this->once())
            ->method('getKey')
            ->will($this->returnValue($this->ticketKey));

        $this->message
            ->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($this->headers));

        $this->headers
            ->expects($this->once())
            ->method('addTextHeader')
            ->with($this->equalTo('In-Reply-To'), $this->equalTo(' <some_value.' . $this->senderHost . '>'))
            ->will($this->returnValue(null));

        $this->uniqueId
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('some_value'));

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->message);

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with('diamante_desk.email_notification')
            ->will($this->returnValue(true));

        $this->ticketWasDeletedSubscriber->onTicketWasDeleted($this->ticketWasDeletedEvent);
    }
} 