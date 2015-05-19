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
namespace Diamante\UserBundle\Tests\EventListener;

use Diamante\DeskBundle\Model\Shared\DomainEvent;

class Event implements DomainEvent
{
    private $eventName;

    private $headerText;

    public function __construct($eventName, $headerText)
    {
        $this->eventName = $eventName;
        $this->headerText = $headerText;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return $this->headerText;
    }
}
