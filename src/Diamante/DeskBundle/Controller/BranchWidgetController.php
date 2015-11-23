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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Diamante\DeskBundle\Controller\WidgetController;

/**
 * @Route("branches")
 */
class BranchWidgetController extends WidgetController
{
    /**
     * @Route(
     *      "/deleteBranchForm/{id}",
     *      name="diamante_branch_delete_form",
     *      requirements={"id"="\d+"}
     * )
     * @Template("DiamanteDeskBundle:Branch/widgets:deleteForm.html.twig")
     *
     */
    public function deleteBranchForm($id)
    {
        try {
            $form = $this->createForm('diamante_delete_branch_form', array('id' => $id));

            if (true === $this->widgetRedirectRequested()) {
                $response = array('form' => $form->createView());
                return $response;
            }

            $this->handle($form);
            $data = $form->getData();

            $tickets = array();
            $newBranchId = $data['newBranch'];
            $branchService = $this->get('diamante.branch.service');

            if ($data['moveTickets']) {
                $tickets = $this->getDoctrine()
                    ->getRepository('DiamanteDeskBundle:Ticket')
                    ->findBy(array('branch' => $id));
            }

            foreach ($tickets as $ticket) {
                $command = $this->get('diamante.command_factory')
                    ->createMoveTicketCommand($ticket);

                $command->branch = $branchService->getBranch($newBranchId);

                if ($command->branch->getId() != $ticket->getBranch()->getId()) {
                    $this->get('diamante.ticket.service')->moveTicket($command);
                }
            }

            $branchService->deleteBranch($id);
            $this->addSuccessMessage('diamante.desk.branch.messages.delete.success');
            $response = $this->getWidgetResponse();
            $response['redirect'] = $this->generateUrl('diamante_branch_list');

        } catch (\Exception $e) {
            $this->handleException($e);
            $response = array('form' => $form->createView());
        }
        return $response;
    }
}
