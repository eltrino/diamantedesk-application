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
namespace Diamante\DeskBundle\Tests\EventListener;

/**
 * Class EventTrait
 *
 * @package Diamante\DeskBundle\Tests\EventListener
 */
trait EventTrait
{
    /**
     * @return Event
     */
    private function event()
    {
        $attachments = array('file.ext');
        $changes = array('Description' => 'New Description');
        $aggregateId = '1g2';
        $eventName = 'DummyEventName';
        $headerTxt = 'Text description about event';
        $subject = 'Dummy Subject';

        return new Event($attachments, $changes, $aggregateId, $eventName, $headerTxt, $subject);
    }
}