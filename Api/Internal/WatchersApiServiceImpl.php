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
use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Model\Entity\Exception\EntityNotFoundException;
use Diamante\DeskBundle\Model\Ticket\WatcherList;
use Diamante\UserBundle\Model\User;
use Diamante\EmailProcessingBundle\Model\Message\MessageRecipient;

class WatchersApiServiceImpl extends WatchersServiceImpl implements RestServiceInterface
{
    use ApiServiceImplTrait;

    /**
     * List watchers from ticket
     *
     * @ApiDoc(
     *  description="List watchers from ticket",
     *  uri="/tickets/{id}/watchers.{_format}",
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
     *      403="Returned when the user is not authorized",
     *      404="Returned when the ticket not found"
     *  }
     * )
     *
     * @param int $id
     * @return array
     */
    public function listWatchers($id)
    {
        $ticket = $this->getTicket($id);
        $watchers = [];
        foreach ($ticket->getWatcherList() as $watcher) {
            $user = User::fromString($watcher->getUserType());
            $watchers[] = $this->userService->fetchUserDetails($user);
        }
        return $watchers;
    }


    /**
     * Add Watcher to Ticket
     *
     * @ApiDoc(
     *  description="Add watcher to ticket by email address",
     *  uri="/tickets/{id}/watchers.{_format}",
     *  method="POST",
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
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized",
     *      404="Returned when the ticket not found"
     *  }
     * )
     *
     * @param Command\AddWatcherByEmailCommand $command
     * @return null
     */
    public function addWatcherByEmail(Command\AddWatcherByEmailCommand $command)
    {
        $ticket = $this->getTicket($command);

        $oroUser = $this->userManager->findUserByEmail($command->email);
        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($command->email);

        // TODO: same login present in notification should be moved to one place
        if ($oroUser) {
            $user = new User($oroUser->getId(), User::TYPE_ORO);
        } elseif ($diamanteUser) {
            $user = new User($diamanteUser->getId(), User::TYPE_DIAMANTE);
        } else {
            $recipient = new MessageRecipient($command->email, null);
            $diamanteUser = $this->diamanteUserFactory->create($command->email, $recipient->getFirstName(),
                $recipient->getLastName());
            $this->diamanteUserRepository->store($diamanteUser);
            $user = new User($diamanteUser->getId(), User::TYPE_DIAMANTE);
        }

        parent::addWatcher($ticket, $user);
        return $this->userService->fetchUserDetails($user);
    }

    /**
     * Delete Watcher from ticket
     *
     * @ApiDoc(
     *  description="Delete watcher",
     *  uri="/tickets/{id}/watchers/{userId}.{_format}",
     *  method="DELETE",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      },
     *      {
     *          "name"="userId",
     *          "dataType"="string",
     *          "description"="User id"
     *      }
     *  },
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized",
     *      404="Returned when the ticket not found"
     *  }
     * )
     *
     * @param Command\RemoveWatcherCommand $command
     * @return null
     */
    public function removeWatcherById(Command\RemoveWatcherCommand $command)
    {
        $ticket = $this->getTicket($command);
        /** @var WatcherList $watcher */
        foreach ($ticket->getWatcherList() as $watcher) {
            if ($watcher->getUserType() == $command->userId) {
                $user = User::fromString($command->userId);
                $this->removeWatcher($ticket, $user);
                break;
            }
        }
    }

    protected function getTicket($command)
    {
        if (is_object($command)) {
            $ticket = $this->ticketRepository->get($command->id);
        } else {
            $ticket = $this->ticketRepository->get($command);
        }

        if (!$ticket) {
            throw new EntityNotFoundException('Ticket not found');
        }

        return $ticket;
    }
}
