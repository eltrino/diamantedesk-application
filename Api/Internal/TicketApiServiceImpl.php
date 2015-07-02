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
namespace Diamante\DeskBundle\Api\Internal;

use Diamante\ApiBundle\Annotation\ApiDoc;
use Diamante\ApiBundle\Routing\RestServiceInterface;
use Diamante\DeskBundle\Api\ApiPagingService;
use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Diamante\DeskBundle\Api\Command\RemoveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Diamante\DeskBundle\Model\Ticket\Filter\TicketFilterCriteriaProcessor;
use Diamante\DeskBundle\Model\Ticket\TicketSearchProcessor;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Diamante\UserBundle\Model\User;
use Diamante\DeskBundle\Model\Shared\Repository;

class TicketApiServiceImpl extends TicketServiceImpl implements RestServiceInterface
{
    /**
     * @var ApiPagingService
     */
    private $apiPagingService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var Repository
     */
    private $branchRepository;

    use ApiServiceImplTrait;

    /**
     * Load Ticket by given ticket id
     *
     * @ApiDoc(
     *  description="Returns a ticket",
     *  uri="/tickets/{id}.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to see ticket",
     *      404="Returned when the ticket is not found"
     *  }
     * )
     *
     * @param int $id
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function loadTicket($id)
    {
        return parent::loadTicket($id);
    }

    /**
     * Load Ticket by given Ticket Key
     *
     * @ApiDoc(
     *  description="Returns a ticket by ticket key",
     *  uri="/tickets/{key}.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="key",
     *          "dataType"="string",
     *          "description"="Ticket Key"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to see ticket",
     *      404="Returned when the ticket is not found"
     *  }
     * )
     *
     * @param string $key
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function loadTicketByKey($key)
    {
        return parent::loadTicketByKey($key);
    }

    /**
     * List Ticket attachments
     *
     * @ApiDoc(
     *  description="Returns ticket attachments",
     *  uri="/tickets/{id}/attachments.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to list ticket attachments"
     *  }
     * )
     *
     * @param int $id
     * @return array|\Diamante\DeskBundle\Model\Attachment\Attachment[]
     */
    public function listTicketAttachments($id)
    {
        return parent::listTicketAttachments($id);
    }

    /**
     * Retrieves Ticket Attachment
     *
     * @ApiDoc(
     *  description="Returns a ticket attachment",
     *  uri="/tickets/{ticketId}/attachments/{attachmentId}.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="ticketId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      },
     *      {
     *          "name"="attachmentId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket attachment Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to see ticket attachment",
     *      404="Returned when the attachment is not found"
     *  }
     * )
     *
     * @param RetrieveTicketAttachmentCommand $command
     * @return \Diamante\DeskBundle\Model\Attachment\Attachment
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function getTicketAttachment(RetrieveTicketAttachmentCommand $command)
    {
        return parent::getTicketAttachment($command);
    }

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
        if (empty($command->assignee)) {
            $branch = $this->branchRepository->get((integer)$command->branch);
            if ($branch && $branch->getDefaultAssignee()) {
                $command->assignee = $branch->getDefaultAssignee()->getId();
            }
        }
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
     * @return array
     */
    public function addAttachmentsForTicket(AddTicketAttachmentCommand $command)
    {
        $this->prepareAttachmentInput($command);
        return parent::addAttachmentsForTicket($command);
    }

    /**
     * Remove Attachment from Ticket
     *
     * @ApiDoc(
     *  description="Remove ticket attachment",
     *  uri="/tickets/{ticketId}/attachments/{attachmentId}.{_format}",
     *  method="DELETE",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="ticketId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      },
     *      {
     *          "name"="attachmentId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Attachment Id"
     *      }
     *  },
     *  statusCodes={
     *      204="Returned when successful",
     *      403="Returned when the user is not authorized to delete attachment",
     *      404="Returned when the ticket or attachment is not found"
     *  }
     * )
     *
     * @param RemoveTicketAttachmentCommand $command
     * @return string $ticketKey
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function removeAttachmentFromTicket(RemoveTicketAttachmentCommand $command)
    {
        return parent::removeAttachmentFromTicket($command);
    }

    /**
     * Delete Ticket by id
     *
     * @ApiDoc(
     *  description="Delete ticket",
     *  uri="/tickets/{id}.{_format}",
     *  method="DELETE",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      }
     *  },
     *  statusCodes={
     *      204="Returned when successful",
     *      403="Returned when the user is not authorized to delete ticket",
     *      404="Returned when the ticket is not found"
     *  }
     * )
     *
     * @param $id
     * @return null
     * @throws \RuntimeException if unable to load required ticket
     */
    public function deleteTicket($id)
    {
        parent::deleteTicket($id);
    }

    /**
     * Delete Ticket by key
     *
     * @ApiDoc(
     *  description="Delete ticket by key",
     *  uri="/tickets/{key}.{_format}",
     *  method="DELETE",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="key",
     *          "dataType"="string",
     *          "description"="Ticket Key"
     *      }
     *  },
     *  statusCodes={
     *      204="Returned when successful",
     *      403="Returned when the user is not authorized to delete ticket",
     *      404="Returned when the ticket is not found"
     *  }
     * )
     *
     * @param string $key
     * @return void
     */
    public function deleteTicketByKey($key)
    {
        parent::deleteTicketByKey($key);
    }

    /**
     * Update certain properties of the Ticket
     *
     * @ApiDoc(
     *  description="Update ticket",
     *  uri="/tickets/{id}.{_format}",
     *  method={
     *      "PUT",
     *      "PATCH"
     *  },
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to update ticket",
     *      404="Returned when the ticket is not found"
     *  }
     * )
     *
     * @param Command\UpdatePropertiesCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function updateProperties(Command\UpdatePropertiesCommand $command)
    {
        return parent::updateProperties($command);
    }

    /**
     * Update certain properties of the Ticket by key
     *
     * @ApiDoc(
     *  description="Update ticket by key",
     *  uri="/tickets/{key}.{_format}",
     *  method={
     *      "PUT",
     *      "PATCH"
     *  },
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="key",
     *          "dataType"="string",
     *          "description"="Ticket Key"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to update ticket",
     *      404="Returned when the ticket is not found"
     *  }
     * )
     *
     * @param Command\UpdatePropertiesCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function updatePropertiesByKey(Command\UpdatePropertiesCommand $command)
    {
        return parent::updatePropertiesByKey($command);
    }

    /**
     * Retrieves list of all Tickets. Performs filtering of tickets if provided with criteria as GET parameters.
     * Time filtering parameters as well as paging/sorting configuration parameters can be found in \Diamante\DeskBundle\Api\Command\Filter\CommonFilterCommand class.
     * Time filtering values should be converted to UTC
     *
     * @ApiDoc(
     *  description="Returns all tickets.",
     *  uri="/tickets.{_format}",
     *  method="GET",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to list tickets"
     *  }
     * )
     *
     * @param Command\Filter\FilterTicketsCommand $ticketFilterCommand
     * @return \Diamante\DeskBundle\Entity\Ticket[]
     */
    public function listAllTickets(Command\Filter\FilterTicketsCommand $ticketFilterCommand)
    {
        $criteriaProcessor = new TicketFilterCriteriaProcessor();
        $criteriaProcessor->setCommand($ticketFilterCommand);
        $criteria = $criteriaProcessor->getCriteria();
        $pagingProperties = $criteriaProcessor->getPagingProperties();
        $repository = $this->getTicketRepository();

        $user = $this->getAuthorizationService()->getLoggedUser();

        if ($user instanceof ApiUser) {
            $user = $this->userService->getUserFromApiUser($user);
        }

        $tickets = $repository->filter($criteria, $pagingProperties, $user);

        try {
            $pagingInfo = $this->apiPagingService->getPagingInfo($repository, $pagingProperties, $criteria);
            $this->populatePagingHeaders($this->apiPagingService, $pagingInfo);
        } catch (\Exception $e) {
        }

        return $tickets;
    }

    /**
     * Retrieves list of Tickets found by query. Ticket is searched by subject and description.
     * Performs filtering of tickets if provided with criteria as GET parameters.
     * Time filtering parameters as well as paging/sorting configuration parameters can be found in \Diamante\DeskBundle\Api\Command\Filter\CommonFilterCommand class.
     * Time filtering values should be converted to UTC
     *
     * @ApiDoc(
     *  description="Returns found tickets.",
     *  uri="/tickets/search.{_format}",
     *  method="GET",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to search tickets"
     *  }
     * )
     *
     * @param Command\SearchTicketsCommand $searchTicketsCommand
     * @return \Diamante\DeskBundle\Entity\Ticket[]
     */
    public function searchTickets(Command\SearchTicketsCommand $searchTicketsCommand)
    {
        $searchProcessor = new TicketSearchProcessor();
        $searchProcessor->setCommand($searchTicketsCommand);

        $query = $searchProcessor->getSearchQuery();

        $criteria = $searchProcessor->getCriteria();

        $pagingProperties = $searchProcessor->getPagingProperties();

        $repository = $this->getTicketRepository();
        $tickets = $repository->search($query, $criteria, $pagingProperties);

        try {
            $pagingInfo = $this->apiPagingService->getPagingInfo($repository, $pagingProperties, $criteria);
            $this->populatePagingHeaders($this->apiPagingService, $pagingInfo);
        } catch (\Exception $e) {
        }

        return $tickets;
    }

    /**
     * @param ApiPagingService $pagingService
     */
    public function setApiPagingService(ApiPagingService $pagingService)
    {
        $this->apiPagingService = $pagingService;
    }

    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param Repository $branchRepository
     */
    public function setBranchRepository(Repository $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    /**
     * Retrieves Person (Provider or Assignee) Data based on typed ID provided
     *
     * @ApiDoc(
     *  description="Returns person data",
     *  uri="/ticket/{id}/reporter.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *       {
     *           "name"="id",
     *           "dataType"="integer",
     *           "requirement"="\d+",
     *           "description"="Ticket Id"
     *       }
     *   },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to view tickets"
     *  }
     * )
     *
     * @param $id
     * @return array
     */
    public function getReporterForTicket($id)
    {
        $ticket = parent::loadTicket($id);
        $details = $this->userService->fetchUserDetails($ticket->getReporter());

        return $details;
    }

    /**
     * Retrieves Person (Provider or Assignee) Data based on typed ID provided
     *
     * @ApiDoc(
     *  description="Returns person data",
     *  uri="/ticket/{id}/assignee.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *       {
     *           "name"="id",
     *           "dataType"="integer",
     *           "requirement"="\d+",
     *           "description"="Ticket Id"
     *       }
     *   },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to view tickets"
     *  }
     * )
     *
     * @param $id
     * @return array
     */
    public function getAssigneeForTicket($id)
    {
        $ticket = parent::loadTicket($id);
        $assignee = $ticket->getAssignee();
        $details = [];

        if (!empty($assignee)) {
            $assigneeAdapter = new User($assignee->getId(), User::TYPE_ORO);
            $details = $this->userService->fetchUserDetails($assigneeAdapter);
        }

        return $details;
    }
}
