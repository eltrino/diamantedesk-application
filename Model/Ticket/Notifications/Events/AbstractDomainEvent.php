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
namespace Diamante\DeskBundle\Model\Ticket\Notifications\Events;

use Diamante\DeskBundle\Model\Shared\DomainEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractDomainEvent extends Event implements DomainEvent
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $changes;

    public function __construct(ArrayCollection $changes)
    {
        $this->changes = $changes;
    }

    /**
     * @return ArrayCollection
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @return string
     */
    abstract function getEventName();
} 