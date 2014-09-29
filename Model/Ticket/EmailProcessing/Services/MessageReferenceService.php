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
namespace Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services;

use Diamante\DeskBundle\Api\Command\CreateCommentFromMessageCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketFromMessageCommand;

interface MessageReferenceService
{
    /**
     * Creates Ticket and Message Reference fot it
     * @param CreateTicketFromMessageCommand $command
     * @return Diamante\DeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket(CreateTicketFromMessageCommand $command);

    /**
     * Creates Comment for Ticket
     * @param CreateCommentFromMessageCommand $command
     * @return void
     */
    public function createCommentForTicket(CreateCommentFromMessageCommand $command);
}
