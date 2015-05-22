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
namespace Diamante\UserBundle\Tests\Infrastructure\User\Notifications;

use Diamante\UserBundle\Infrastructure\User\Notifications\EmailNotifier;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference;
use Diamante\DeskBundle\Model\Shared\Email\TemplateResolver;
use Diamante\UserBundle\Model\ApiUser\Notifications\UserNotification;
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
use Diamante\UserBundle\Model\ApiUser\ApiUser;

class EmailNotifierTest extends \PHPUnit_Framework_TestCase
{
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
     * @var \Oro\Bundle\ConfigBundle\Config\ConfigManager
     * @Mock \Oro\Bundle\ConfigBundle\Config\ConfigManager
     */
    private $configManager;

    /**
     * @var string
     */
    private $senderEmail = 'sender@host.com';

    /**
     * @var \Diamante\UserBundle\Model\DiamanteUser
     */
    private $diamanteUser;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->diamanteUser = new DiamanteUser('reporter@host.com', 'First', 'Last');
    }

    public function testNotify()
    {
        $author = new ApiUser('reporter@host.com', 'password');
        $notification = new UserNotification($author, 'Header');
        $format = '%prefix% %first_name% %middle_name% %last_name% %suffix%';

        $message = new \Swift_Message();

        $this->mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $this->configManager->expects($this->once())->method('get')->will($this->returnValue('Mike The Bot'));
        $this->nameFormatter->expects($this->any())->method('format')->with($this->diamanteUser)->will(
            $this->returnValue('First Last')
        );

        $this->userService
            ->expects($this->once())
            ->method('verifyDiamanteUserExists')
            ->with($this->equalTo($author->getEmail()))
            ->will($this->returnValue(1));

        $this->userService
            ->expects($this->once())
            ->method('getByUser')
            ->with($this->equalTo(new UserAdapter(1, UserAdapter::TYPE_DIAMANTE)))
            ->will($this->returnValue($this->diamanteUser));

        $this->nameFormatter
            ->expects($this->once())
            ->method('getNameFormat')
            ->will($this->returnValue($format));

        $this->templateResolver->expects($this->any())->method('resolve')->will(
            $this->returnValueMap(
                array(
                    array($notification, TemplateResolver::TYPE_TXT, 'txt.template.html'),
                    array($notification, TemplateResolver::TYPE_HTML, 'html.template.html')
                )
            )
        );

        $optionsConstraint = $this->logicalAnd(
            $this->arrayHasKey('user'),
            $this->arrayHasKey('header'),
            $this->contains('First  Last'),
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

                        return false !== strpos($other->getBody(), 'Rendered TXT template')
                        && array_key_exists('reporter@host.com', $to);
                    }
                )
            )
        );

        $notifier = new EmailNotifier(
            $this->twig,
            $this->mailer,
            $this->templateResolver,
            $this->userService,
            $this->nameFormatter,
            $this->configManager,
            $this->senderEmail
        );

        $notifier->notify($notification);
    }
}