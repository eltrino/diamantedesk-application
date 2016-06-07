<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\Infrastructure\Ticket;

use Diamante\DeskBundle\Infrastructure\Shared\Entity\AbstractPropertyHandler;

class TicketPropertyHandler extends AbstractPropertyHandler
{
    const TICKET_TYPE = 'ticket';

    public function getName()
    {
        return static::TICKET_TYPE;
    }

    protected function getByStatusType(array $target)
    {
        return $this->getPropertyInstanceValue($target);
    }

    protected function getByBranchType(array $target)
    {
        $property = $target[$this->property];


        return $property->getValue();
    }

    protected function getByPriorityType(array $target)
    {
        return $this->getWeightable($target);
    }

    protected function getBySourceType(array $target)
    {
        return $this->getPropertyInstanceValue($target);
    }
}
