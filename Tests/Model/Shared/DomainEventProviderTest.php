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
namespace Diamante\DeskBundle\Tests\Model\Shared;

use Diamante\DeskBundle\Model\Shared\DomainEventProvider;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class DomainEventProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventProvider
     */
    private $eventProvider;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\DomainEvent
     * @Mock \Diamante\DeskBundle\Model\Shared\DomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->eventProvider = new DomainEventProvider();
    }

    public function testGetRecordedEvents()
    {
        $events = $this->eventProvider->getRecordedEvents();
        $this->assertEquals(array(), $events);

        $this->eventProvider->raise($this->domainEvent);
        $this->eventProvider->raise($this->domainEvent);
        $events = $this->eventProvider->getRecordedEvents();
        $this->assertEquals(2, count($events));

        $events = $this->eventProvider->getRecordedEvents();
        $this->assertEquals(array(), $events);
    }
} 