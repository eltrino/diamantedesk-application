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

use Diamante\AutomationBundle\Rule\Condition\AbstractCondition;
use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Infrastructure\Shared\Entity\AbstractPropertyHandler;

class TicketPropertyHandler extends AbstractPropertyHandler
{
    const TICKET_TYPE = 'ticket';
    const UNASSIGNED = 'unassigned';

    /**
     * @return string
     */
    public function getName()
    {
        return static::TICKET_TYPE;
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getByStatusType(array $target)
    {
        return $this->getPropertyInstanceValue($target);
    }

    /**
     * @param array $target
     *
     * @return mixed
     */
    protected function getByBranchType(array $target)
    {
        $property = $target[$this->property];
        $mode = $this->context->getMode();

        if (AbstractCondition::SOFT == $mode) {
            /** @var Branch $property */
            $value = $property->getName();
        } else {
            /** @var Branch $property */
            $value = $property->getId();
        }

        return $value;
    }

    /**
     * @param array $target
     *
     * @return int
     */
    protected function getByPriorityType(array $target)
    {
        return $this->getWeightable($target);
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getBySourceType(array $target)
    {
        return $this->getPropertyInstanceValue($target);
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getAssignee(array $target)
    {
        $value = $this->getByUserType($target);

        if (empty($value)) {
            $value = self::UNASSIGNED;
        }

        return $value;
    }
}
