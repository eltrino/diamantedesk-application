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

use Diamante\DeskBundle\Infrastructure\Ticket\Notifications\EmailTemplateResolver;
use Diamante\DeskBundle\Model\Shared\Email\TemplateResolver;
use Diamante\DeskBundle\Model\Ticket\Notifications\TicketNotification;

class EmailTemplateResolverTest extends \PHPUnit_Framework_TestCase
{
    use \Diamante\DeskBundle\Tests\EventListener\EventTrait;

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Give type is invalid.
     */
    public function testResolve()
    {
        $notification = new TicketNotification(
            'unique_id',
            1,
            'Header',
            'Subject',
            new \ArrayIterator(array('key' => 'value')),
            array('file.ext'),
            $this->event()
        );

        $resolver = new EmailTemplateResolver();

        $this->assertEquals(
            'DiamanteDeskBundle:Ticket/notification:notification.txt.twig',
            $resolver->resolve($notification, TemplateResolver::TYPE_TXT)
        );
        $this->assertEquals(
            'DiamanteDeskBundle:Ticket/notification:notification.html.twig',
            $resolver->resolve($notification, TemplateResolver::TYPE_HTML)
        );

        $resolver->resolve($notification, 'fake_type');
    }
}
