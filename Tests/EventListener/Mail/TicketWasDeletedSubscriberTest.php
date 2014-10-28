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
    private $recipientsList = array(
        'no-reply.reporter@example.com',
        'no-reply.assignee@example.com',
    );

    /**
     * @var \Oro\Bundle\UserBundle\Entity\User
     * @Mock \Oro\Bundle\UserBundle\Entity\User
     */
    private $user;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->senderEmail = 'no-reply@example.com';

        $this->ticketWasDeletedSubscriber = new TicketWasDeletedSubscriber(
            $this->twig,
            $this->mailer,
            $this->securityFacade,
            $this->senderEmail
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
            ->expects($this->exactly(2))
            ->method('getAggregateId')
            ->will($this->returnValue('id'));

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

        $this->ticketWasDeletedSubscriber->onTicketWasDeleted($this->ticketWasDeletedEvent);
    }
} 