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
namespace Eltrino\DiamanteDeskBundle\Datagrid;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;

class StatusHelper
{
    /**
     * @return array
     */
    public function getTicketStatuses()
    {
        return
            array(
                Status::NEW_ONE     => Status::LABEL_NEW_ONE,
                Status::OPEN        => Status::LABEL_OPEN,
                Status::PENDING     => Status::LABEL_PENDING,
                Status::IN_PROGRESS => Status::LABEL_IN_PROGRESS,
                Status::CLOSED      => Status::LABEL_CLOSED,
                Status::ON_HOLD     => Status::LABEL_ON_HOLD
            );
    }

    /**
     * @return string
     */
    public function getOpenStatus()
    {
        return Status::OPEN;
    }

    public function getNewStatus()
    {
        return Status::NEW_ONE;
    }

} 