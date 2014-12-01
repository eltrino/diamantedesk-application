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

use Diamante\DeskBundle\EventListener\Mail\CommentWasDeletedSubscriber;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class CommentWasDeletedSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommentWasDeletedSubscriber
     */
    private $commentWasDeletedSubscriber;

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
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Events\CommentWasDeleted
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Events\CommentWasDeleted
     */
    private $commentWasDeletedEvent;

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

    /**
     * @var \Oro\Bundle\ConfigBundle\Config\ConfigManager
     * @Mock \Oro\Bundle\ConfigBundle\Config\ConfigManager
     */
    private $configManager;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->senderEmail = 'no-reply@example.com';

        $this->commentWasDeletedSubscriber = new CommentWasDeletedSubscriber(
            $this->twig,
            $this->mailer,
            $this->securityFacade,
            $this->configManager,
            $this->senderEmail
        );
    }

    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->commentWasDeletedSubscriber);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(
                'commentWasDeleted' => 'onCommentWasDeleted',
            ),
            $this->commentWasDeletedSubscriber->getSubscribedEvents()
        );
    }

    public function testOnCommentWasDeleted()
    {
        $this->commentWasDeletedEvent
            ->expects($this->atLeastOnce())
            ->method('getSubject')
            ->will($this->returnValue('Subject'));

        $this->commentWasDeletedEvent
            ->expects($this->any())
            ->method('getRecipientsList')
            ->will($this->returnValue($this->recipientsList));

        $this->commentWasDeletedEvent
            ->expects($this->atLeastOnce())
            ->method('getCommentContent')
            ->will($this->returnValue('Comment Content'));

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
            'comment' => 'Comment Content',
            'user'    => $userFullName,
            'header'  => 'Comment was deleted'
        );

        $templates = array(
            'txt'  => 'DiamanteDeskBundle:Comment/notification/mails/delete:notification.txt.twig',
            'html' => 'DiamanteDeskBundle:Comment/notification/mails/delete:notification.html.twig'
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

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with('diamante_desk.email_notification')
            ->will($this->returnValue(true));

        $this->commentWasDeletedSubscriber->onCommentWasDeleted($this->commentWasDeletedEvent);
    }
} 