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
namespace Diamante\DeskBundle\Controller;

use Diamante\DeskBundle\Form\Type\AddWatcherType;
use Diamante\DeskBundle\Form\Type\AssigneeTicketType;
use Diamante\DeskBundle\Form\Type\MassAddWatcherTicketType;
use Diamante\DeskBundle\Form\Type\MassAssigneeTicketType;
use Diamante\DeskBundle\Form\Type\MassChangeTicketStatusType;
use Diamante\DeskBundle\Form\Type\MassMoveTicketType;
use Diamante\DeskBundle\Form\Type\MoveTicketType;
use Diamante\DeskBundle\Form\Type\UpdateTicketStatusType;
use Diamante\DeskBundle\Model\Branch\Exception\BranchNotFoundException;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketNotFoundException;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("tickets")
 */
class TicketWidgetController extends WidgetController
{
    /**
     * @Route(
     *      "/status/ticket/{id}",
     *      name="diamante_ticket_status_change",
     *      requirements={"id"="\d+"}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/info.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function changeStatusWidgetAction(Request $request, $id)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
            $command = $this->get('diamante.command_factory')
                ->createUpdateStatusCommandForView($ticket);
            $form = $this->createForm(UpdateTicketStatusType::class, $command);

            if (true === $this->widgetRedirectRequested($request)) {
                $response = ['form' => $form->createView()];

                return $response;
            }

            $this->handle($request, $form);
            $this->get('diamante.ticket.service')->updateStatus($command);
            $this->addSuccessMessage('diamante.desk.ticket.messages.change_status.success');
            $response = ['saved' => true];

        } catch (\Exception $e) {
            $this->handleException($e);
            $response = ['form' => $form->createView()];
        }

        return $response;
    }

    /**
     * @Route(
     *      "/move/ticket/{id}",
     *      name="diamante_ticket_move",
     *      requirements={"id"="\d+"}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/move.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function moveWidgetAction(Request $request, $id)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
            $command = $this->get('diamante.command_factory')
                ->createMoveTicketCommand($ticket);
            $form = $this->createForm(MoveTicketType::class, $command);

            if (true === $this->widgetRedirectRequested($request)) {
                $response = ['form' => $form->createView()];

                return $response;
            }
            $this->handle($request, $form);
            if ($command->branch->getId() !== $ticket->getBranch()->getId()) {
                $this->get('diamante.ticket.service')->moveTicket($command);
                $this->addSuccessMessage('diamante.desk.ticket.messages.move.success');

                return $this->getWidgetResponse('diamante_ticket_view', ['key' => $ticket->getKey()]);
            }
            $response = $this->getWidgetResponse();
        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse('diamante_ticket_list');
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        }

        return $response;
    }

    /**
     * @Route(
     *      "/watcher/ticket/{ticketId}",
     *      name="diamante_add_watcher",
     *      requirements={"ticketId"="\d+"}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/add_watcher.html.twig")
     *
     * @param int $ticketId
     *
     * @return array
     */
    public function addWatcherWidgetAction(Request $request, $ticketId)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($ticketId);
            $command = $this->get('diamante.command_factory')
                ->addWatcherCommand($ticket);
            $form = $this->createForm(AddWatcherType::class, $command);

            if (true === $this->widgetRedirectRequested($request)) {
                return ['form' => $form->createView()];

            }
            $this->handle($request, $form);

            if (is_string($command->watcher)) {
                $user = new DiamanteUser($command->watcher);
                $this->get('diamante.user.repository')->store($user);
                $command->watcher = new User($user->getId(), User::TYPE_DIAMANTE);
            }

            if ($command->watcher) {
                $this->get('diamante.ticket.watcher_list.service')
                    ->addWatcher($ticket, $command->watcher);
                $this->addSuccessMessage('diamante.desk.ticket.messages.watch.success');
            }
            $response = ['reload_page' => true];
        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse('diamante_ticket_list');
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        }

        return $response;
    }

    /**
     * @Route(
     *      "/assign/{id}",
     *      name="diamante_ticket_assign",
     *      requirements={"id"="\d+"}
     * )
     *
     * @Template("DiamanteDeskBundle:Ticket:widget/assignee.html.twig")
     *
     * @param Request $request
     * @param int $id
     *
     * @return array
     */
    public function assignWidgetAction(Request $request, $id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);

        $command = $this->get('diamante.command_factory')
            ->createAssigneeTicketCommand($ticket);

        $form = $this->createForm(AssigneeTicketType::class, $command);

        if (true === $this->widgetRedirectRequested($request)) {
            $response = ['form' => $form->createView()];

            return $response;
        }

        try {
            $this->handle($request, $form);

            $command->assignee = $command->assignee ? $command->assignee->getId() : null;
            $this->get('diamante.ticket.service')->assignTicket($command);
            $this->addSuccessMessage('diamante.desk.ticket.messages.reassign.success');
            $response = ['saved' => true];

        } catch (\Exception $e) {
            $this->handleException($e);
            $response = ['form' => $form->createView()];
        }

        return $response;
    }

    /**
     * @Route(
     *       "/watchers/ticket/{ticket}",
     *      name="diamante_ticket_watchers",
     *      requirements={"ticket"="\d+"}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/watchers.html.twig")
     *
     * @param Ticket $ticket
     *
     * @return array
     */
    public function watchersAction($ticket)
    {
        $ticket = $this->container->get('diamante.ticket.repository')->get($ticket);
        $users = [];

        foreach ($ticket->getWatcherList() as $watcher) {
            $users[] = User::fromString($watcher->getUserType());
        }

        return [
            'ticket'   => $ticket,
            'watchers' => $users,
        ];
    }

    /**
     * @Route(
     *      "/assignMass",
     *      name="diamante_ticket_mass_assign",
     *      options= {"expose"= true}
     * )
     *
     * @Template("DiamanteDeskBundle:Ticket:widget/massAssignee.html.twig")
     *
     * @return array
     */
    public function assignMassAction(Request $request)
    {
        try {
            $values = $request->get('values');
            $inset = $request->get('inset');
            $command = $this->get('diamante.command_factory')
                ->createMassAssigneeTicketCommand($values, $inset);

            $form = $this->createForm(MassAssigneeTicketType::class, $command);

            if (true === $this->widgetRedirectRequested($request)) {
                return ['form' => $form->createView()];
            }

            $form->handleRequest($request);
            $requestAssign = $request->get('assignee');
            $ids = $request->get('ids');

            if (!isset($requestAssign)) {
                $assignee = $command->assignee;
            } else {
                $assignee = $requestAssign;
            }

            $ids = explode(',', $ids);

            if ($this->isAllSelected($inset)) {
                $tickets = $this->get('diamante.ticket.repository')->getAll();
                $this->changeAllTicketsAssignee($ids, $tickets, $assignee);
            } else {
                $this->changeSelectedTicketsAssignee($ids, $assignee);
            }

            $this->addSuccessMessage('diamante.desk.ticket.messages.reassign.success');
            $response = ['saved' => true];

        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        }

        return $response;
    }

    protected function changeAllTicketsAssignee($ids, $tickets, $assignee)
    {
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        foreach ($tickets as $ticket) {
            if (in_array($ticket->getId(), $ids)) {
                continue;
            }

            $command = $this->get('diamante.command_factory')
                ->createAssigneeTicketCommand($ticket);

            $command->assignee = $assignee;
            $this->get('diamante.ticket.service')->assignTicket($command);
        }
    }

    protected function changeSelectedTicketsAssignee($ids, $assignee)
    {
        foreach ($ids as $id) {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
            $command = $this->get('diamante.command_factory')
                ->createAssigneeTicketCommand($ticket);

            $command->assignee = $assignee;
            $this->get('diamante.ticket.service')->assignTicket($command);
        }
    }

    /**
     * @Route(
     *      "/changeStatusMass",
     *      name="diamante_ticket_mass_status_change",
     *      options = {"expose" = true}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/massChangeStatus.html.twig")
     *
     * @return array
     */
    public function changeStatusMassAction(Request $request)
    {
        try {
            $values = $request->get('values');
            $inset = $request->get('inset');
            $command = $this->get('diamante.command_factory')
                ->createChangeStatusMassCommand($values, $inset);

            $form = $this->createForm(MassChangeTicketStatusType::class, $command);

            if (true === $this->widgetRedirectRequested($request)) {
                return ['form' => $form->createView()];
            }

            $form->handleRequest($request);
            $requestStatus = $request->get('status');
            $ids = $request->get('ids');

            if (!isset($requestStatus)) {
                $status = $command->status;
            } else {
                $status = $requestStatus;
            }

            $ids = explode(',', $ids);

            if ($this->isAllSelected($inset)) {
                $tickets = $this->get('diamante.ticket.repository')->getAll();
                $this->changeAllTicketsStatus($ids, $tickets, $status);
            } else {
                $this->changeSelectedTicketsStatus($ids, $status);
            }

            $this->addSuccessMessage('diamante.desk.ticket.messages.change_status.success');
            $response = ['saved' => true];

        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        }

        return $response;
    }

    /**
     * @param array $ids
     * @param array $tickets
     * @param       $status
     */
    protected function changeAllTicketsStatus(array $ids, array $tickets, $status)
    {
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        foreach ($tickets as $ticket) {
            if (in_array($ticket->getId(), $ids)) {
                continue;
            }

            $command = $this->get('diamante.command_factory')
                ->createUpdateStatusCommandForView($ticket);

            $command->status = $status;
            $this->get('diamante.ticket.service')->updateStatus($command);
        }
    }

    /**
     * @param array $ids
     * @param       $status
     */
    protected function changeSelectedTicketsStatus(array $ids, $status)
    {
        foreach ($ids as $id) {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
            $command = $this->get('diamante.command_factory')
                ->createUpdateStatusCommandForView($ticket);

            $command->status = $status;
            $this->get('diamante.ticket.service')->updateStatus($command);
        }
    }

    /**
     * @Route(
     *      "/moveMass",
     *      name="diamante_ticket_mass_move",
     *      options = {"expose" = true}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/massMove.html.twig")
     *
     * @return array
     */
    public function moveMassAction(Request $request)
    {
        try {
            $values = $request->get('values');
            $inset = $request->get('inset');
            $command = $this->get('diamante.command_factory')
                ->createMassMoveTicketCommand($values, $inset);

            $form = $this->createForm(MassMoveTicketType::class, $command);

            if (true === $this->widgetRedirectRequested($request)) {
                return ['form' => $form->createView()];
            }

            $form->handleRequest($request);
            $requestBranch = $request->get('branch');
            $ids = $request->get('ids');

            if (!isset($requestBranch)) {
                $branch = $command->branch;
            } else {
                $branch = $requestBranch;
            }

            $ids = explode(",", $ids);
            if ($this->isAllSelected($inset)) {
                $tickets = $this->get('diamante.ticket.repository')->getAll();
                $this->moveAllTickets($ids, $tickets, $branch);
            } else {
                $this->moveSelectedTickets($ids, $branch);
            }

            $this->addSuccessMessage('diamante.desk.ticket.messages.move.success');
            $response = ['reload_page' => true, 'saved' => true];

        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        } catch (BranchNotFoundException $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        }

        return $response;
    }

    /**
     * @param array $ids
     * @param array $tickets
     * @param       $branch
     */
    protected function moveAllTickets(array $ids, array $tickets, $branch)
    {
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        foreach ($tickets as $ticket) {
            if (in_array($ticket->getId(), $ids)) {
                continue;
            }

            $command = $this->get('diamante.command_factory')
                ->createMoveTicketCommand($ticket);

            $command->branch = $this->get('diamante.branch.service')->getBranch($branch);

            if ($command->branch->getId() != $ticket->getBranch()->getId()) {
                $this->get('diamante.ticket.service')->moveTicket($command);
            }
        }
    }

    /**
     * @param $ids
     * @param $branch
     */
    protected function moveSelectedTickets(array $ids, $branch)
    {
        foreach ($ids as $id) {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
            $command = $this->get('diamante.command_factory')
                ->createMoveTicketCommand($ticket);

            $command->branch = $this->get('diamante.branch.service')->getBranch($branch);

            if ($command->branch->getId() != $ticket->getBranch()->getId()) {
                $this->get('diamante.ticket.service')->moveTicket($command);
            }
        }
    }

    /**
     * @Route(
     *      "/addWatcherMass",
     *      name="diamante_ticket_mass_add_watcher",
     *      options = {"expose" = true}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/massAddWatcher.html.twig")
     *
     * @return array
     */
    public function addWatcherMassAction(Request $request)
    {
        try {
            $values = $request->get('values');
            $inset = $request->get('inset');
            $command = $this->get('diamante.command_factory')
                ->createMassAddWatcherCommand($values, $inset);

            $form = $this->createForm(MassAddWatcherTicketType::class, $command);

            if (true === $this->widgetRedirectRequested($request)) {
                return ['form' => $form->createView()];
            }

            $form->handleRequest($request);
            $requestWatcher = $request->get('branch');
            $ids = $request->get('ids');

            if (!isset($requestWatcher)) {
                $watcher = $command->watcher;
            } else {
                $watcher = $requestWatcher;
            }

            $ids = explode(",", $ids);

            if ($this->isAllSelected($inset)) {
                $tickets = $this->get('diamante.ticket.repository')->getAll();
                $this->watchAllTickets($ids, $tickets, $watcher);
            } else {
                $this->watchSelectedTickets($ids, $watcher);
            }


            $this->addSuccessMessage('diamante.desk.ticket.messages.watch.success');
            $response = ['reload_page' => true];

        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        }

        return $response;
    }

    /**
     * @param array $ids
     * @param array $tickets
     * @param       $watcher
     */
    protected function watchAllTickets(array $ids, array $tickets, $watcher)
    {
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        foreach ($tickets as $ticket) {
            if (in_array($ticket->getId(), $ids)) {
                continue;
            }

            $command = $this->get('diamante.command_factory')
                ->addWatcherCommand($ticket);

            $command->watcher = $watcher;
            $this->get('diamante.ticket.watcher_list.service')
                ->addWatcher($ticket, $command->watcher);
        }
    }

    /**
     * @param array $ids
     * @param       $watcher
     */
    protected function watchSelectedTickets(array $ids, $watcher)
    {
        foreach ($ids as $id) {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
            $command = $this->get('diamante.command_factory')
                ->addWatcherCommand($ticket);

            $command->watcher = $watcher;
            $this->get('diamante.ticket.watcher_list.service')
                ->addWatcher($ticket, $command->watcher);
        }
    }

    protected function isAllSelected($inset)
    {
        return $inset === '0';
    }
}
