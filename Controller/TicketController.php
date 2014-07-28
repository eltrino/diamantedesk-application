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
namespace Eltrino\DiamanteDeskBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Util\Inflector;

use Doctrine\ORM\EntityManager;
use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FileDto;
use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Form\Command\AttachmentCommand;
use Eltrino\DiamanteDeskBundle\Form\Command\CreateticketCommand;
use Eltrino\DiamanteDeskBundle\Form\CommandFactory;
use Eltrino\DiamanteDeskBundle\Form\Type\AssigneeTicketType;
use Eltrino\DiamanteDeskBundle\Form\Type\AttachmentType;
use Eltrino\DiamanteDeskBundle\Form\Type\CreateTicketType;
use Eltrino\DiamanteDeskBundle\Form\Type\UpdateTicketStatusType;
use Eltrino\DiamanteDeskBundle\Form\Type\UpdateTicketType;
use Eltrino\DiamanteDeskBundle\Ticket\Api\TicketService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("tickets")
 */
class TicketController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="diamante_ticket_list",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     */
    public function listAction()
    {
        $filtersGenerator = $this->container->get('diamante.ticket.internal.grid_filters_service');

        $filtersList = $filtersGenerator->getFilters();
        $linksList = array();
        $baseUri = $this->getRequest()->getBaseUrl() . $this->getRequest()->getPathInfo();
        foreach($filtersList as $filter) {
            $link['name'] =  $filter->getName();
            $link['url'] = '#url=' . $baseUri . $filtersGenerator->generateGridFilterUrl($filter->getId());
            $linksList[] = $link;
        }

        return ['linksList' => $linksList];
    }

    /**
     * @Route(
     *      "/view/{id}",
     *      name="diamante_ticket_view",
     *      requirements={"id"="\d+"}
     * )
     * @Template
     */
    public function viewAction(Ticket $ticket)
    {
        return ['entity'  => $ticket];
    }

    /**
     * @Route(
     *      "/status/ticket/{id}",
     *      name="diamante_ticket_status_change",
     *      requirements={"id"="\d+"}
     * )
     * @Template("EltrinoDiamanteDeskBundle:Ticket:widget/info.html.twig")
     */
    public function changeStatusAction(Ticket $ticket)
    {
        $redirect = ($this->getRequest()->get('no_redirect')) ? false : true;

        $command = $this->get('diamante.command_factory')
            ->createUpdateStatusCommandForView($ticket);

        $form = $this->createForm(new UpdateTicketStatusType(), $command);

        if (false === $redirect) {
            try {
                $this->handle($form);
                $this->get('diamante.ticket.service')
                    ->updateStatus(
                        $command->ticketId,
                        $command->status
                    );
                $this->addSuccessMessage('Status successfully changed');
                $response = array('saved' => true);
            } catch (\LogicException $e) {
                $response = array('form' => $form->createView());
            }
        } else {
            $response = array('form' => $form->createView());
        }

        return $response;
    }

    /**
     * @Route(
     *      "/create/{id}",
     *      name="diamante_ticket_create",
     *      requirements={"id" = "\d+"},
     *      defaults={"id" = null}
     * )
     * @Template("EltrinoDiamanteDeskBundle:Ticket:create.html.twig")
     *
     * @param Branch $branch
     * @return array
     */
    public function createAction(Branch $branch = null)
    {
        $command = $this->get('diamante.command_factory')
            ->createCreateTicketCommand($branch, $this->getUser());

        $response = null;
        $form = $this->createForm(new CreateTicketType(), $command);
        try {
            $this->handle($form);
            $ticket = $this->get('diamante.ticket.service')
                ->createTicket(
                    $command->branch->getId(),
                    $command->subject,
                    $command->description,
                    $command->reporter->getId(),
                    $command->assignee->getId(),
                    $command->status
                );
            $this->addSuccessMessage('Ticket successfully created.');
            $response = $this->getSuccessSaveResponse($ticket);
        } catch (\LogicException $e) {
            $response = array('form' => $form->createView());
        } catch (\Exception $e) {
            $this->addErrorMessage($e->getMessage());
            $response = array('form' => $form->createView());
        }
        return $response;
    }

    /**
     * @Route(
     *      "/update/{id}",
     *      name="diamante_ticket_update",
     *      requirements={"id"="\d+"}
     * )
     *
     * @Template("EltrinoDiamanteDeskBundle:Ticket:update.html.twig")
     *
     * @param Ticket $ticket
     * @return array
     */
    public function updateAction(Ticket $ticket)
    {
        $command = $this->get('diamante.command_factory')
            ->createUpdateTicketCommand($ticket);

        $response = null;
        $form = $this->createForm(new UpdateTicketType(), $command);
        try {
            $this->handle($form);
            $ticket = $this->get('diamante.ticket.service')
                ->updateTicket(
                    $command->id,
                    $command->subject,
                    $command->description,
                    $command->reporter->getId(),
                    $command->assignee->getId(),
                    $command->status
                );
            $this->addSuccessMessage('Ticket successfully saved.');
            $response = $this->getSuccessSaveResponse($ticket);
        } catch (\LogicException $e) {
            $response = array('form' => $form->createView());
        } catch (\Exception $e) {
            $this->addErrorMessage($e->getMessage());
            $response = array('form' => $form->createView());
        }
        return $response;
    }

    /**
     * @Route(
     *      "/delete/{id}",
     *      name="diamante_ticket_delete",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param Ticket $ticket
     * @return Response
     */
    public function deleteAction(Ticket $ticket)
    {
        $this->get('diamante.ticket.service')
            ->deleteTicket($ticket->getId());

        $this->addSuccessMessage('Ticket successfully deleted.');

        return $this->redirect(
            $this->generateUrl('diamante_ticket_list')
        );
    }

    /**
     * @Route(
     *      "/assign/{id}",
     *      name="diamante_ticket_assign",
     *      requirements={"id"="\d+"}
     * )
     *
     * @Template("EltrinoDiamanteDeskBundle:Ticket:assign.html.twig")
     *
     * @param Ticket $ticket
     * @return array
     */
    public function assignAction(Ticket $ticket)
    {
        $command = $this->get('diamante.command_factory')
            ->createAssigneeTicketCommand($ticket);

        $response = null;
        $form = $this->createForm(new AssigneeTicketType(), $command);
        try {
            $this->handle($form);
            $ticket = $this->get('diamante.ticket.service')
                ->assignTicket(
                    $command->id,
                    $command->assignee->getId()
                );
            $this->addSuccessMessage('Ticket successfully re-assigned.');
            $response = $this->getSuccessSaveResponse($ticket);
        } catch (\LogicException $e) {
            $this->addErrorMessage($e->getMessage());
            $response = array('form' => $form->createView());
        }
        return $response;
    }

    /**
     * @Route(
     *      "/attach/ticket/{id}",
     *      name="diamante_ticket_create_attach",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param Ticket $ticket
     * @Template
     */
    public function attachAction(Ticket $ticket)
    {
        $commandFactory = new CommandFactory();
        $form = $this->createForm(new AttachmentType(), $commandFactory->createAttachmentCommand($ticket));
        $formView = $form->createView();
        $formView->children['files']->vars = array_replace($formView->children['files']->vars, array('full_name' => 'diamante_attachment_form[files][]'));
        return array('form' => $formView);
    }

    /**
     * @Route(
     *      "/attachPost/ticket/{id}",
     *      name="diamante_ticket_create_attach_post",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param Ticket $ticket
     * @Template
     */
    public function attachPostAction(Ticket $ticket)
    {
        $response = null;
        $commandFactory = new CommandFactory();
        $form = $this->createForm(new AttachmentType(), $commandFactory->createAttachmentCommand($ticket));
        $formView = $form->createView();
        $formView->children['files']->vars = array_replace($formView->children['files']->vars, array('full_name' => 'diamante_attachment_form[files][]'));
        try {
            $this->handle($form);

            /** @var AttachmentCommand $command */
            $command = $form->getData();

            /** @var TicketService $ticketService */
            $ticketService = $this->get('diamante.ticket.service');

            $fileDtoArray = array();
            foreach ($command->files as $file) {
                $fileDtoArray[] = FileDto::createFromUploadedFile($file);
            }

            $filesListDto = new FilesListDto();
            $filesListDto->setFiles($fileDtoArray);

            $ticketService->addAttachmentsForTicket($filesListDto, $ticket->getId());

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Attachment(s) successfully uploaded.')
            );
            $response = $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'diamante_attachment_attach',
                    'parameters' => array(),
                ),
                array(
                    'route' => 'diamante_ticket_view',
                    'parameters' => array('id' => $ticket->getId())
                )
            );
        } catch (Exception $e) {
            $response = array('form' => $formView);
        }
        return $response;
    }

    /**
     * @Route(
     *      "/attachment/remove/ticket/{ticketId}/attachment/{attachId}",
     *      name="diamante_ticket_attachment_remove",
     *      requirements={"ticketId"="\d+", "attachId"="\d+"}
     * )
     *
     * @param integer $ticketId
     * @param integer $ticketId
     * @param integer $attachmentId
     * @Template
     */
    public function removeAttachmentAction($ticketId, $attachId)
    {
        /** @var TicketService $ticketService */
        $ticketService = $this->get('diamante.ticket.service');
        $ticketService->removeAttachmentFromTicket($ticketId, $attachId);
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('Attachment successfully deleted.')
        );
        $response = $this->redirect($this->generateUrl(
            'diamante_ticket_view',
            array('id' => $ticketId)
        ));
        return $response;
    }

    /**
     * @Route(
     *      "/attachment/download/ticket/{ticketId}/attachment/{attachId}",
     *      name="diamante_ticket_attachment_download",
     *      requirements={"ticketId"="\d+", "attachId"="\d+"}
     * )
     * @return Reponse
     * @todo refactor download logic
     */
    public function downloadAttachmentAction($ticketId, $attachId)
    {
        /** @var TicketService $ticketService */
        $ticketService = $this->get('diamante.ticket.service');
        $attachment = $ticketService->getTicketAttachment($ticketId, $attachId);

        $filePathname = realpath($this->container->getParameter('kernel.root_dir').'/attachment')
            . '/' . $attachment->getFilename();

        if (!file_exists($filePathname)) {
            throw $this->createNotFoundException('Attachment not found');
        }

        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($filePathname);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $attachment->getFilename(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $attachment->getFilename())
        );

        return $response;
    }

    /**
     * @Route(
            "/attachment/list/{id}",
     *      name="diamant_ticket_widget_attachment_list",
     *      requirements={"id"="\d+"}
     * )
     * @Template("EltrinoDiamanteDeskBundle:Ticket:attachment/list.html.twig")
     */
    public function attachmentList(Ticket $ticket)
    {
        return [
            'ticket_id' => $ticket->getId(),
            'attachments' => $ticket->getAttachments()
        ];
    }

    /**
     * @param Form $form
     * @throws \LogicException
     * @throws \RuntimeException
     */
    private function handle(Form $form)
    {
        if (false === $this->getRequest()->isMethod('POST')) {
            throw new \LogicException('Form can be posted only by "POST" method.');
        }

        $form->handleRequest($this->getRequest());

        if (false === $form->isValid()) {
            throw new \RuntimeException('Form object validation failed, form is invalid.');
        }
    }

    /**
     * @param $message
     */
    private function addSuccessMessage($message)
    {
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans($message)
        );
    }

    private function addErrorMessage($message)
    {
        $this->get('session')->getFlashBag()->add(
            'error',
            $this->get('translator')->trans($message)
        );
    }

    /**
     * @param Ticket $ticket
     * @return array
     */
    private function getSuccessSaveResponse(Ticket $ticket)
    {
        return $this->get('oro_ui.router')->redirectAfterSave(
            ['route' => 'diamante_ticket_update', 'parameters' => ['id' => $ticket->getId()]],
            ['route' => 'diamante_ticket_view', 'parameters' => ['id' => $ticket->getId()]]
        );
    }

    /**
     * @param Ticket $ticket
     * @return string
     */
    private function getViewUrl(Ticket $ticket)
    {
        return $this->generateUrl(
            'diamante_ticket_view',
            array('id' => $ticket->getId())
        );
    }
}
