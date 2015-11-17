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

use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketNotFoundException;
use Diamante\UserBundle\Model\User;
use Diamante\UserBundle\Entity\DiamanteUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Diamante\DeskBundle\Model\Branch\Exception\BranchNotFoundException;
use Diamante\DeskBundle\Controller\WidgetController;

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
     * @return array
     */
    public function changeStatusWidgetAction($id)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
            $command = $this->get('diamante.command_factory')
                ->createUpdateStatusCommandForView($ticket);
            $form = $this->createForm('diamante_ticket_status_form', $command);

            if (true === $this->widgetRedirectRequested()) {
                $response = array('form' => $form->createView());
                return $response;
            }

            $this->handle($form);
            $this->get('diamante.ticket.service')->updateStatus($command);
            $this->addSuccessMessage('diamante.desk.ticket.messages.change_status.success');
            $response = array('saved' => true);

        } catch (\Exception $e) {
            $this->handleException($e);
            $response = array('form' => $form->createView());
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
     * @return array
     */
    public function moveWidgetAction($id)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
            $command = $this->get('diamante.command_factory')
                ->createMoveTicketCommand($ticket);
            $form = $this->createForm('diamante_ticket_form_move', $command);

            if (true === $this->widgetRedirectRequested()) {
                $response = array('form' => $form->createView());
                return $response;
            }
            $this->handle($form);
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
     * @return array
     */
    public function addWatcherWidgetAction($ticketId)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($ticketId);
            $command = $this->get('diamante.command_factory')
                ->addWatcherCommand($ticket);
            $form = $this->createForm('diamante_add_watcher_form', $command);

            if (true === $this->widgetRedirectRequested()) {
                return array('form' => $form->createView());

            }
            $this->handle($form);

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
     * @param int $id
     * @return array
     */
    public function assignWidgetAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);

        $command = $this->get('diamante.command_factory')
            ->createAssigneeTicketCommand($ticket);

        $form = $this->createForm('diamante_ticket_form_assignee', $command);

        if (true === $this->widgetRedirectRequested()) {
            $response = array('form' => $form->createView());

            return $response;
        }

        try {
            $this->handle($form);

            $command->assignee = $command->assignee ? $command->assignee->getId() : null;
            $this->get('diamante.ticket.service')->assignTicket($command);
            $this->addSuccessMessage('diamante.desk.ticket.messages.reassign.success');
            $response = array('saved' => true);

        } catch (\Exception $e) {
            $this->handleException($e);
            $response = array('form' => $form->createView());
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
    public function assignMassAction()
    {
        try {
            $command = $this->get('diamante.command_factory')
                ->createMassAssigneeTicketCommand($this->getRequest()->get('values'));

            $form = $this->createForm('diamante_ticket_form_mass_assignee', $command);

            if (true === $this->widgetRedirectRequested()) {
                return array('form' => $form->createView());
            }

            $form->handleRequest($this->getRequest());
            $requestAssign = $this->getRequest()->get('assignee');

            if (!isset($requestAssign)) {
                $assignee = $command->assignee;
            } else {
                $assignee = $requestAssign;
            }

            $ids = explode(",", $this->getRequest()->get('ids'));

            foreach ($ids as $id) {
                $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
                $command = $this->get('diamante.command_factory')
                    ->createAssigneeTicketCommand($ticket);

                $command->assignee = $assignee;
                $this->get('diamante.ticket.service')->assignTicket($command);
            }

            $this->addSuccessMessage('diamante.desk.ticket.messages.reassign.success');
            $response = array('saved' => true);

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
     * @Route(
     *      "/changeStatusMass",
     *      name="diamante_ticket_mass_status_change",
     *      options = {"expose" = true}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/massChangeStatus.html.twig")
     *
     * @return array
     */
    public function changeStatusMassAction()
    {
        try {
            $command = $this->get('diamante.command_factory')
                ->createChangeStatusMassCommand($this->getRequest()->get('values'));

            $form = $this->createForm('diamante_ticket_form_status_mass_change', $command);

            if (true === $this->widgetRedirectRequested()) {
                return array('form' => $form->createView());
            }

            $form->handleRequest($this->getRequest());
            $requestStatus = $this->getRequest()->get('status');

            if (!isset($requestStatus)) {
                $status = $command->status;
            } else {
                $status = $requestStatus;
            }

            $ids = explode(",", $this->getRequest()->get('ids'));

            foreach ($ids as $id) {
                $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
                $command = $this->get('diamante.command_factory')
                    ->createUpdateStatusCommandForView($ticket);

                $command->status = $status;
                $this->get('diamante.ticket.service')->updateStatus($command);
            }

            $this->addSuccessMessage('diamante.desk.ticket.messages.change_status.success');
            $response = array('saved' => true);

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
     * @Route(
     *      "/moveMass",
     *      name="diamante_ticket_mass_move",
     *      options = {"expose" = true}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/massMove.html.twig")
     *
     * @return array
     */
    public function moveMassAction()
    {
        try {
            $command = $this->get('diamante.command_factory')
                ->createMassMoveTicketCommand($this->getRequest()->get('values'));

            $form = $this->createForm('diamante_ticket_form_mass_move', $command);

            if (true === $this->widgetRedirectRequested()) {
                return array('form' => $form->createView());
            }

            $form->handleRequest($this->getRequest());
            $requestBranch = $this->getRequest()->get('branch');

            if (!isset($requestBranch)) {
                $branch = $command->branch;
            } else {
                $branch = $requestBranch;
            }

            $ids = explode(",", $this->getRequest()->get('ids'));

            foreach ($ids as $id) {
                $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
                $command = $this->get('diamante.command_factory')
                    ->createMoveTicketCommand($ticket);

                $command->branch = $this->get('diamante.branch.service')->getBranch($branch);

                if ($command->branch->getId() != $ticket->getBranch()->getId()) {
                    $this->get('diamante.ticket.service')->moveTicket($command);
                }
            }

            $this->addSuccessMessage('diamante.desk.ticket.messages.move.success');
            $response = array('reload_page' => true, 'saved' => true);

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
     * @Route(
     *      "/addWatcherMass",
     *      name="diamante_ticket_mass_add_watcher",
     *      options = {"expose" = true}
     * )
     * @Template("DiamanteDeskBundle:Ticket:widget/massAddWatcher.html.twig")
     *
     * @return array
     */
    public function addWatcherMassAction()
    {
        try {
            $command = $this->get('diamante.command_factory')
                ->createMassAddWatcherCommand($this->getRequest()->get('values'));

            $form = $this->createForm('diamante_ticket_form_mass_add_watcher', $command);

            if (true === $this->widgetRedirectRequested()) {
                return array('form' => $form->createView());
            }

            $form->handleRequest($this->getRequest());
            $requestWatcher = $this->getRequest()->get('branch');

            if (!isset($requestWatcher)) {
                $watcher = $command->watcher;
            } else {
                $watcher = $requestWatcher;
            }

            $ids = explode(",", $this->getRequest()->get('ids'));

            foreach ($ids as $id) {
                $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
                $command = $this->get('diamante.command_factory')
                    ->addWatcherCommand($ticket);

                $command->watcher = $watcher;
                $this->get('diamante.ticket.watcher_list.service')
                    ->addWatcher($ticket, $command->watcher);
            }

            $this->addSuccessMessage('diamante.desk.ticket.messages.watch.success');
            $response = array('reload_page' => true);

        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getWidgetResponse();
        }

        return $response;
    }
}
