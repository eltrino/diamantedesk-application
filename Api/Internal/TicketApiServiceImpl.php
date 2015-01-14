<?php

namespace Diamante\DeskBundle\Api\Internal;

use Diamante\ApiBundle\Annotation\ApiDoc;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;

class TicketApiServiceImpl extends TicketServiceImpl
{
    use ApiServiceImplTrait;

    /**
     * Create Ticket
     *
     * @ApiDoc(
     *  description="Create ticket",
     *  uri="/tickets.{_format}",
     *  method="POST",
     *  resource=true,
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized to create ticket"
     *  }
     * )
     *
     * @param CreateTicketCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function createTicket(CreateTicketCommand $command)
    {
        $this->prepareAttachmentInput($command);
        return parent::createTicket($command);
    }

    /**
     * Adds Attachments for Ticket
     *
     * @ApiDoc(
     *  description="Add attachment to ticket",
     *  uri="/tickets/{ticketId}/attachments.{_format}",
     *  method="POST",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="ticketId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      }
     *  },
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized to add attachment to ticket"
     *  }
     * )
     *
     * @param AddTicketAttachmentCommand $command
     * @return void
     */
    public function addAttachmentsForTicket(AddTicketAttachmentCommand $command)
    {
        $this->prepareAttachmentInput($command);
        parent::addAttachmentsForTicket($command);
    }
}
