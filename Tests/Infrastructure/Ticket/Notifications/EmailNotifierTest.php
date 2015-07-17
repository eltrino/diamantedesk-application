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
namespace Diamante\DeskBundle\Tests\Infrastructure\Ticket\Notifications;

use Diamante\DeskBundle\Infrastructure\Ticket\Notifications\EmailNotifier;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference;
use Diamante\DeskBundle\Model\Shared\Email\TemplateResolver;
use Diamante\DeskBundle\Model\Ticket\Notifications\TicketNotification;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Entity\DiamanteUser;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;
use Diamante\UserBundle\Model\User as UserAdapter;
use Diamante\DeskBundle\Entity\WatcherList;

class EmailNotifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     * @Mock \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

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
     * @var \Diamante\DeskBundle\Model\Shared\Email\TemplateResolver
     * @Mock \Diamante\DeskBundle\Model\Shared\Email\TemplateResolver
     */
    private $templateResolver;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\TicketRepository
     * @Mock \Diamante\DeskBundle\Model\Ticket\TicketRepository
     */
    private $ticketRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository
     * @Mock \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository
     */
    private $messageReferenceRepository;

    /**
     * @var \Diamante\UserBundle\Api\UserService
     * @Mock \Diamante\UserBundle\Api\UserService
     */
    private $userService;

    /**
     * @var \Oro\Bundle\LocaleBundle\Formatter\NameFormatter
     * @Mock \Oro\Bundle\LocaleBundle\Formatter\NameFormatter
     */
    private $nameFormatter;

    /**
     * @var \Diamante\UserBundle\Infrastructure\DiamanteUserRepository
     * @Mock \Diamante\UserBundle\Infrastructure\DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var \Oro\Bundle\ConfigBundle\Config\ConfigManager
     * @Mock \Oro\Bundle\ConfigBundle\Config\ConfigManager
     */
    private $configManager;

    /**
     * @var string
     */
    private $senderEmail = 'sender@host.com';

    /**
     * @var string
     */
    private $senderHost = 'host.com';

    /**
     * @var \Diamante\UserBundle\Model\DiamanteUser
     */
    private $diamanteUser;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\UserManager
     * @Mock \Oro\Bundle\UserBundle\Entity\UserManager
     */
    private $oroUserManager;

    /**
     * @var \Diamante\DeskBundle\Api\WatchersService
     * @Mock \Diamante\DeskBundle\Api\WatchersService
     */
    private $watchersService;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->diamanteUser = new DiamanteUser('reporter@host.com', 'First', 'Last');
    }

    public function testNotifyOroUser()
    {
        $ticketUniqueId = UniqueId::generate();
        $reporter = new UserAdapter(1, UserAdapter::TYPE_DIAMANTE);
        $assignee = new User();
        $assignee->setId(2);
        $assignee->setEmail('assignee@host.com');
        $author = new UserAdapter(1, UserAdapter::TYPE_DIAMANTE);
        $branch = new Branch('KEY', 'Name', 'Description');
        $ticket = new Ticket(
            $ticketUniqueId, new TicketSequenceNumber(1), 'Subject', 'Description', $branch, $reporter, $assignee,
            new Source(Source::WEB), new Priority(Priority::PRIORITY_MEDIUM), new Status(Status::NEW_ONE)
        );
        $notification = new TicketNotification(
            (string)$ticketUniqueId,
            $author,
            'Header',
            'Subject',
            new \ArrayIterator(array('key' => 'value')),
            array('file.ext')
        );

        $message = new \Swift_Message();

        $this->watchersService->expects($this->once())->method('getWatchers')->will(
            $this->returnValue([new WatcherList($ticket, 'oro_1')])
        );
        $this->oroUserManager->expects($this->once())->method('findUserBy')->with(['id' => 1])->will(
            $this->returnValue($assignee)
        );
        $this->configManager->expects($this->once())->method('get')->will(
            $this->returnValue('test@gmail.com')
        );
        $this->nameFormatter->expects($this->once())->method('format')->with($this->diamanteUser)->will(
            $this->returnValue('First Last')
        );
        $this->mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $this->ticketRepository->expects($this->once())->method('getByUniqueId')->with($ticketUniqueId)
            ->will($this->returnValue($ticket));

        $this->userService
            ->expects($this->once())
            ->method('getByUser')
            ->with($this->equalTo($author))
            ->will($this->returnValue($this->diamanteUser));

        $this->templateResolver->expects($this->any())->method('resolve')->will(
            $this->returnValueMap(
                array(
                    array($notification, TemplateResolver::TYPE_TXT, 'txt.template.html'),
                    array($notification, TemplateResolver::TYPE_HTML, 'html.template.html')
                )
            )
        );

        $optionsConstraint = $this->logicalAnd(
            $this->arrayHasKey('changes'),
            $this->arrayHasKey('attachments'),
            $this->arrayHasKey('user'),
            $this->arrayHasKey('header'),
            $this->contains($notification->getChangeList()),
            $this->contains($notification->getAttachments()),
            $this->contains('First Last'),
            $this->contains($notification->getHeaderText())
        );

        $this->twig->expects($this->at(0))->method('render')->with('txt.template.html', $optionsConstraint)
            ->will($this->returnValue('Rendered TXT template'));
        $this->twig->expects($this->at(1))->method('render')->with('html.template.html', $optionsConstraint)
            ->will($this->returnValue('Rendered HTML template'));

        $this->mailer->expects($this->once())->method('send')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Swift_Message'),
                $this->callback(
                    function (\Swift_Message $other) use ($notification) {
                        $to = $other->getTo();

                        return false !== strpos($other->getSubject(), $notification->getSubject())
                        && false !== strpos($other->getSubject(), 'KEY-1')
                        && false !== strpos($other->getBody(), 'Rendered TXT template')
                        && array_key_exists('assignee@host.com', $to)
                        && $other->getHeaders()->has('References')
                        && false !== strpos($other->getHeaders()->get('References'), 'id_1@host.com')
                        && false !== strpos($other->getHeaders()->get('References'), 'id_2@host.com');
                    }
                )
            )
        );

        $this->messageReferenceRepository->expects($this->once())->method('findAllByTicket')->with($ticket)
            ->will(
                $this->returnValue(
                    array(
                        new MessageReference('id_1@host.com', $ticket),
                        new MessageReference('id_2@host.com', $ticket)
                    )
                )
            );

        $this->messageReferenceRepository->expects($this->once())->method('store')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference')
            )
        );

        $notifier = new EmailNotifier(
            $this->container, $this->twig, $this->mailer, $this->templateResolver, $this->ticketRepository,
            $this->messageReferenceRepository, $this->userService, $this->nameFormatter,
            $this->diamanteUserRepository, $this->configManager, $this->oroUserManager,
            $this->watchersService, $this->senderHost
        );

        $notifier->notify($notification);
    }

    public function testNotifyDiamanteUser()
    {
        $ticketUniqueId = UniqueId::generate();
        $reporter = new UserAdapter(1, UserAdapter::TYPE_DIAMANTE);
        $assignee = new User();
        $assignee->setId(2);
        $assignee->setEmail('assignee@host.com');
        $author = new UserAdapter(1, UserAdapter::TYPE_DIAMANTE);
        $branch = new Branch('KEY', 'Name', 'Description');
        $ticket = new Ticket(
            $ticketUniqueId, new TicketSequenceNumber(1), 'Subject', 'Description', $branch, $reporter, $assignee,
            new Source(Source::WEB), new Priority(Priority::PRIORITY_MEDIUM), new Status(Status::NEW_ONE)
        );
        $notification = new TicketNotification(
            (string)$ticketUniqueId,
            $author,
            'Header',
            'Subject',
            new \ArrayIterator(array('key' => 'value')),
            array('file.ext')
        );

        $message = new \Swift_Message();

        $this->watchersService->expects($this->once())->method('getWatchers')->will(
            $this->returnValue([new WatcherList($ticket, 'diamante_1')])
        );

        $this->diamanteUserRepository->expects($this->once())->method('get')->with(1)->will(
            $this->returnValue($assignee)
        );
        $this->configManager->expects($this->once())->method('get')->will(
            $this->returnValue('test@gmail.com')
        );
        $this->nameFormatter->expects($this->once())->method('format')->with($this->diamanteUser)->will(
            $this->returnValue('First Last')
        );
        $this->mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $this->ticketRepository->expects($this->once())->method('getByUniqueId')->with($ticketUniqueId)
            ->will($this->returnValue($ticket));

        $this->userService
            ->expects($this->once())
            ->method('getByUser')
            ->with($this->equalTo($author))
            ->will($this->returnValue($this->diamanteUser));

        $this->templateResolver->expects($this->any())->method('resolve')->will(
            $this->returnValueMap(
                array(
                    array($notification, TemplateResolver::TYPE_TXT, 'txt.template.html'),
                    array($notification, TemplateResolver::TYPE_HTML, 'html.template.html')
                )
            )
        );

        $optionsConstraint = $this->logicalAnd(
            $this->arrayHasKey('changes'),
            $this->arrayHasKey('attachments'),
            $this->arrayHasKey('user'),
            $this->arrayHasKey('header'),
            $this->contains($notification->getChangeList()),
            $this->contains($notification->getAttachments()),
            $this->contains('First Last'),
            $this->contains($notification->getHeaderText())
        );

        $this->twig->expects($this->at(0))->method('render')->with('txt.template.html', $optionsConstraint)
            ->will($this->returnValue('Rendered TXT template'));
        $this->twig->expects($this->at(1))->method('render')->with('html.template.html', $optionsConstraint)
            ->will($this->returnValue('Rendered HTML template'));

        $this->mailer->expects($this->once())->method('send')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Swift_Message'),
                $this->callback(
                    function (\Swift_Message $other) use ($notification) {
                        $to = $other->getTo();

                        return false !== strpos($other->getSubject(), $notification->getSubject())
                        && false !== strpos($other->getSubject(), 'KEY-1')
                        && false !== strpos($other->getBody(), 'Rendered TXT template')
                        && array_key_exists('assignee@host.com', $to)
                        && $other->getHeaders()->has('References')
                        && false !== strpos($other->getHeaders()->get('References'), 'id_1@host.com')
                        && false !== strpos($other->getHeaders()->get('References'), 'id_2@host.com');
                    }
                )
            )
        );

        $this->messageReferenceRepository->expects($this->once())->method('findAllByTicket')->with($ticket)
            ->will(
                $this->returnValue(
                    array(
                        new MessageReference('id_1@host.com', $ticket),
                        new MessageReference('id_2@host.com', $ticket)
                    )
                )
            );

        $this->messageReferenceRepository->expects($this->once())->method('store')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference')
            )
        );

        $notifier = new EmailNotifier(
            $this->container, $this->twig, $this->mailer, $this->templateResolver, $this->ticketRepository,
            $this->messageReferenceRepository, $this->userService, $this->nameFormatter,
            $this->diamanteUserRepository, $this->configManager, $this->oroUserManager,
            $this->watchersService, $this->senderHost
        );

        $notifier->notify($notification);
    }

    public function testNotifyWithEmptyAuthor()
    {
        $ticketUniqueId = UniqueId::generate();
        $reporter = new UserAdapter(1, UserAdapter::TYPE_DIAMANTE);
        $assignee = new User();
        $assignee->setId(2);
        $assignee->setEmail('assignee@host.com');
        $author = null;
        $branch = new Branch('KEY', 'Name', 'Description');
        $ticket = new Ticket(
            $ticketUniqueId, new TicketSequenceNumber(1), 'Subject', 'Description', $branch, $reporter, $assignee,
            new Source(Source::WEB), new Priority(Priority::PRIORITY_MEDIUM), new Status(Status::NEW_ONE)
        );
        $notification = new TicketNotification(
            (string)$ticketUniqueId,
            $author,
            'Header',
            'Subject',
            new \ArrayIterator(array('key' => 'value')),
            array('file.ext')
        );

        $message = new \Swift_Message();
        $this->watchersService->expects($this->once())->method('getWatchers')->will(
            $this->returnValue([new WatcherList($ticket, 'diamante_1')])
        );

        $this->diamanteUserRepository->expects($this->exactly(2))->method('get')->with(1)->will(
            $this->returnValue($this->diamanteUser)
        );
        $this->configManager->expects($this->once())->method('get')->will(
            $this->returnValue('test@gmail.com')
        );
        $this->nameFormatter->expects($this->once())->method('format')->with($this->diamanteUser)->will(
            $this->returnValue('First Last')
        );
        $this->mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $this->ticketRepository->expects($this->once())->method('getByUniqueId')->with($ticketUniqueId)
            ->will($this->returnValue($ticket));

        $this->templateResolver->expects($this->any())->method('resolve')->will(
            $this->returnValueMap(
                array(
                    array($notification, TemplateResolver::TYPE_TXT, 'txt.template.html'),
                    array($notification, TemplateResolver::TYPE_HTML, 'html.template.html')
                )
            )
        );

        $optionsConstraint = $this->logicalAnd(
            $this->arrayHasKey('changes'),
            $this->arrayHasKey('attachments'),
            $this->arrayHasKey('user'),
            $this->arrayHasKey('header'),
            $this->contains($notification->getChangeList()),
            $this->contains($notification->getAttachments()),
            $this->contains('First Last'),
            $this->contains($notification->getHeaderText())
        );

        $this->twig->expects($this->at(0))->method('render')->with('txt.template.html', $optionsConstraint)
            ->will($this->returnValue('Rendered TXT template'));
        $this->twig->expects($this->at(1))->method('render')->with('html.template.html', $optionsConstraint)
            ->will($this->returnValue('Rendered HTML template'));

        $this->mailer->expects($this->once())->method('send')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Swift_Message'),
                $this->callback(
                    function (\Swift_Message $other) use ($notification) {
                        $to = $other->getTo();

                        return false !== strpos($other->getSubject(), $notification->getSubject())
                        && false !== strpos($other->getSubject(), 'KEY-1')
                        && false !== strpos($other->getBody(), 'Rendered TXT template')
                        && array_key_exists('reporter@host.com', $to)
                        && $other->getHeaders()->has('References')
                        && false !== strpos($other->getHeaders()->get('References'), 'id_1@host.com')
                        && false !== strpos($other->getHeaders()->get('References'), 'id_2@host.com');
                    }
                )
            )
        );

        $this->messageReferenceRepository->expects($this->once())->method('findAllByTicket')->with($ticket)
            ->will(
                $this->returnValue(
                    array(
                        new MessageReference('id_1@host.com', $ticket),
                        new MessageReference('id_2@host.com', $ticket)
                    )
                )
            );

        $this->messageReferenceRepository->expects($this->once())->method('store')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference')
            )
        );

        $notifier = new EmailNotifier(
            $this->container, $this->twig, $this->mailer, $this->templateResolver, $this->ticketRepository,
            $this->messageReferenceRepository, $this->userService, $this->nameFormatter,
            $this->diamanteUserRepository, $this->configManager, $this->oroUserManager,
            $this->watchersService, $this->senderHost
        );

        $notifier->notify($notification);
    }
}
