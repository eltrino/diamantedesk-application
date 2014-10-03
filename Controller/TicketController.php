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

use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Form\CommandFactory;
use Diamante\DeskBundle\Form\Type\AssigneeTicketType;
use Diamante\DeskBundle\Form\Type\AttachmentType;
use Diamante\DeskBundle\Form\Type\CreateTicketType;
use Diamante\DeskBundle\Form\Type\UpdateTicketStatusType;
use Diamante\DeskBundle\Form\Type\UpdateTicketType;
use Diamante\DeskBundle\Ticket\Api\TicketService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Diamante\DeskBundle\Entity\Branch;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

use Diamante\DeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Dto\AttachmentDto;

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
     *
     * @return array
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
     *
     * @param int $id
     * @return array
     */
    public function viewAction($id)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicket($id);

            return ['entity'  => $ticket];
        } catch (\Exception $e) {
            $this->addErrorMessage('Ticket loading failed, ticket not found');

            return new Response($e->getMessage(), 404);
        }
    }

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
    public function changeStatusAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
        $redirect = ($this->getRequest()->get('no_redirect')) ? false : true;

        $command = $this->get('diamante.command_factory')
            ->createUpdateStatusCommandForView($ticket);

        $form = $this->createForm(new UpdateTicketStatusType(), $command);

        if (false === $redirect) {
            try {
                $this->handle($form);
                $this->get('diamante.ticket.service')->updateStatus($command);
                $this->addSuccessMessage('diamante.desk.ticket.actions.change_status.success');
                $response = array('saved' => true);
            } catch (\LogicException $e) {
                $this->addErrorMessage('diamante.desk.ticket.actions.change_status.error');
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
     * @Template("DiamanteDeskBundle:Ticket:create.html.twig")
     *
     * @param int $id
     * @return array
     */
    public function createAction($id = null)
    {
        $branch = null;
        if ($id) {
            $branch = $this->get('diamante.branch.service')->getBranch($id);
        }
        $command = $this->get('diamante.command_factory')
            ->createCreateTicketCommand($branch, $this->getUser());

        $response = null;
        $form = $this->createForm(new CreateTicketType(), $command);
        $formView = $form->createView();
        $formView->children['files']->vars = array_replace($formView->children['files']->vars, array('full_name' => 'diamante_ticket_form[files][]'));
        try {
            $this->handle($form);

            $command->branch = $command->branch->getId();
            $command->reporter = $command->reporter->getId();
            $command->assignee = $command->assignee ? $command->assignee->getId() : null;

            $attachments = array();
            foreach ($command->files as $file) {
                if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    array_push($attachments, AttachmentInput::createFromUploadedFile($file));
                }
            }
            $command->attachmentsInput = $attachments;

            $ticket = $this->get('diamante.ticket.service')->createTicket($command);

            $this->addSuccessMessage('diamante.desk.ticket.messages.create.success');
            $response = $this->getSuccessSaveResponse($ticket);
        } catch (\LogicException $e) {
            $this->addErrorMessage('diamante.desk.ticket.messages.create.error');
            $response = array('form' => $formView);
        } catch (\Exception $e) {
            //TODO: Log original exception
            $this->addErrorMessage('diamante.desk.ticket.messages.create.error');
            $response = array('form' => $formView);
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
     * @Template("DiamanteDeskBundle:Ticket:update.html.twig")
     *
     * @param int $id
     * @return array
     */
    public function updateAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
        $command = $this->get('diamante.command_factory')
            ->createUpdateTicketCommand($ticket);

        $response = null;
        $form = $this->createForm(new UpdateTicketType(), $command);
        $formView = $form->createView();
        $formView->children['files']->vars = array_replace($formView->children['files']->vars, array('full_name' => 'diamante_ticket_form[files][]'));
        try {
            $this->handle($form);

            $command->reporter = $command->reporter->getId();
            $command->assignee = $command->assignee ? $command->assignee->getId() : null;

            $attachments = array();
            foreach ($command->files as $file) {
                if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    array_push($attachments, AttachmentInput::createFromUploadedFile($file));
                }
            }
            $command->attachmentsInput = $attachments;

            $ticket = $this->get('diamante.ticket.service')->updateTicket($command);
            $this->addSuccessMessage('diamante.desk.ticket.messages.save.success');
            $response = $this->getSuccessSaveResponse($ticket);
        } catch (\LogicException $e) {
            $this->addErrorMessage('diamante.desk.ticket.messages.save.error');
            $response = array('form' => $formView);
        } catch (\Exception $e) {
            //TODO: Log original error
            $this->addErrorMessage('diamante.desk.ticket.messages.save.error');
            $response = array('form' => $formView);
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
     * @param int $id
     * @return Response
     */
    public function deleteAction($id)
    {
        try {
            $this->get('diamante.ticket.service')->deleteTicket($id);
            $this->addSuccessMessage('diamante.desk.ticket.messages.delete.success');
            return $this->redirect(
                $this->generateUrl('diamante_ticket_list')
            );
        } catch (Exception $e) {
            //TODO: Log original error
            return new Response($this->get('translator')->trans('diamante.desk.ticket.messages.delete.error'), 500);
        }
    }

    /**
     * @Route(
     *      "/assign/{id}",
     *      name="diamante_ticket_assign",
     *      requirements={"id"="\d+"}
     * )
     *
     * @Template("DiamanteDeskBundle:Ticket:assign.html.twig")
     *
     * @param int $id
     * @return array
     */
    public function assignAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
        $command = $this->get('diamante.command_factory')
            ->createAssigneeTicketCommand($ticket);

        $response = null;
        $form = $this->createForm(new AssigneeTicketType(), $command);
        try {
            $this->handle($form);

            $command->assignee = $command->assignee ? $command->assignee->getId() : null;

            $this->get('diamante.ticket.service')->assignTicket($command);
            $this->addSuccessMessage('diamante.desk.ticket.messages.reassign.success');
            $response = $this->getSuccessSaveResponse($ticket);
        } catch (\LogicException $e) {
            //TODO: Log original exception
            $this->addErrorMessage('diamante.desk.ticket.messages.reassign.error');
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
     * @Template
     *
     * @param int $id
     * @return array
     */
    public function attachAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
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
     * @Template
     *
     * @param int $id
     * @return Response
     */
    public function attachPostAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
        $response = null;
        $session = $this->get('session');
        $commandFactory = new CommandFactory();
        $form = $this->createForm(new AttachmentType(), $commandFactory->createAttachmentCommand($ticket));
        $formView = $form->createView();
        $formView->children['files']->vars = array_replace($formView->children['files']->vars, array('full_name' => 'diamante_attachment_form[files][]'));
        $beforeUploadAttachments = $ticket->getAttachments()->toArray();

        try {
            $this->handle($form);

            /** @var AttachmentCommand $command */
            $command = $form->getData();

            $attachments = array();
            foreach ($command->files as $file) {
                array_push($attachments, AttachmentInput::createFromUploadedFile($file));
            }

            /** @var TicketService $ticketService */
            $ticketService = $this->get('diamante.ticket.service');
            $addTicketAttachmentCommand = new AddTicketAttachmentCommand();
            $addTicketAttachmentCommand->attachments = $attachments;
            $addTicketAttachmentCommand->ticketId = $ticket->getId();
            $ticketService->addAttachmentsForTicket($addTicketAttachmentCommand);

            $this->addSuccessMessage('diamante.desk.attachment.messages.create.success');
            if ($this->getRequest()->request->get('diam-dropzone')) {
                $response = new Response();
                try {
                    $afterUploadAttachments = $ticket->getAttachments()->toArray();
                    $uploadedAttachments = $this->getAttachmentsDiff($afterUploadAttachments, $beforeUploadAttachments);

                    foreach ($uploadedAttachments as $att) {
                        $uploadedAttachmentsIds[] = $att->getId();
                    }
                    $session->set('recent_attachments_ids', $uploadedAttachmentsIds);
                    $response->setStatusCode(200);
                } catch (\Exception $e) {
                    $response->setStatusCode(500);
                }
            } else {
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
            }
        } catch (Exception $e) {
            $this->addErrorMessage('diamante.desk.attachment.messages.create.error');
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
     * @Template
     *
     * @param int $ticketId
     * @param int $attachId
     * @return RedirectResponse
     */
    public function removeAttachmentAction($ticketId, $attachId)
    {
        /** @var TicketService $ticketService */
        $ticketService = $this->get('diamante.ticket.service');
        $removeTicketAttachment = new RemoveAttachmentCommand();
        $removeTicketAttachment->entityId     = $ticketId;
        $removeTicketAttachment->attachmentId = $attachId;

        try {
            $ticketService->removeAttachmentFromTicket($removeTicketAttachment);
            $this->addSuccessMessage('diamante.desk.attachment.messages.delete.success');
        } catch (\Exception $e) {
            $this->addErrorMessage('diamante.desk.attachment.messages.delete.error');
        }

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
     *
     * @return BinaryFileResponse
     * @todo refactor download logic
     */
    public function downloadAttachmentAction($ticketId, $attachId)
    {
        /** @var TicketService $ticketService */
        $ticketService = $this->get('diamante.ticket.service');
        $retrieveTicketAttachmentCommand = new RetrieveTicketAttachmentCommand();
        $retrieveTicketAttachmentCommand->ticketId = $ticketId;
        $retrieveTicketAttachmentCommand->attachmentId = $attachId;
        try {
            $attachment = $ticketService->getTicketAttachment($retrieveTicketAttachmentCommand);
            $attachmentDto = AttachmentDto::createFromAttachment($attachment);
            $response = $this->getFileDownloadResponse($attachmentDto);

            return $response;
        } catch (\Exeception $e) {
            $this->addErrorMessage('diamante.desk.attachment.messages.get.error');
            throw $this->createNotFoundException('Attachment not found');
        }
    }

    /**
     * @Route(
     *      "/attachment/latest/ticket/{ticketId}",
     *      name="diamante_ticket_attachment_latest",
     *      requirements={"ticketId"="\d+"}
     * )
     *
     * @param int $ticketId
     * @return JsonResponse
     */
    public function getRecentlyUploadedAttachmentsAction($ticketId)
    {
        $session = $this->get('session');

        if ($session->has('recent_attachments_ids')) {
            $uploadedAttachmentsIds = $session->get('recent_attachments_ids');
            $ticketService = $this->get('diamante.ticket.service');

            foreach ($uploadedAttachmentsIds as $attachmentId) {
                $retrieveTicketAttachmentCommand = new RetrieveTicketAttachmentCommand();
                $retrieveTicketAttachmentCommand->attachmentId = $attachmentId;
                $retrieveTicketAttachmentCommand->ticketId = $ticketId;
                $recentAttachments[] = $ticketService->getTicketAttachment($retrieveTicketAttachmentCommand);
            }

            $list = $this->getRecentAttachmentsList($ticketId, $recentAttachments);
            $response = new JsonResponse($list);
        } else {
            $response = new Response();
            $response->setStatusCode(500);
        }
        $session->remove('recent_attachments_ids');
        return $response;
    }

    /**
     * @Route(
     *       "/attachment/list/{id}",
     *      name="diamante_ticket_widget_attachment_list",
     *      requirements={"id"="\d+"}
     * )
     * @Template("DiamanteDeskBundle:Ticket:attachment/list.html.twig")
     *
     * @param int $id
     * @return array
     */
    public function attachmentList($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
        return [
            'ticket' => $ticket,
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

    /**
     * Get attachments list as JSON
     *
     * @param int $ticketId
     * @param array $attachments
     * @return array
     */
    private function getRecentAttachmentsList($ticketId, $attachments)
    {
        $data = array(
            "result"        => true,
            "attachments"   => array(),
        );

        foreach ($attachments as $attachment) {
            $downloadLink = $this->get('router')->generate(
                'diamante_ticket_attachment_download',
                array('ticketId' => $ticketId, 'attachId' => $attachment->getId())
            );
            $deleteLink = $this->get('router')->generate(
                'diamante_ticket_attachment_remove',
                array('ticketId' => $ticketId, 'attachId' => $attachment->getId())
            );

            if (in_array($attachment->getFile()->getExtension(), array('jpg','png','gif','bmp', 'jpeg'))) {
                $previewLink = $this->get('router')->generate(
                    '_imagine_attach_preview_img',
                    array('path' => $attachment->getFile()->getPathname())
                );
            } else {
                $previewLink = '';
            }

            $data["attachments"][] = array(
                'filename' => $attachment->getFile()->getFileName(),
                'src'      => $previewLink,
                'ext'      => $attachment->getFile()->getExtension(),
                'url'      => $downloadLink,
                'delete'   => $deleteLink,
                'id'       => $attachment->getId(),
            );
        }

        return $data;
    }

    /**
     * Get diff between ticket's attachments before and after upload
     *
     * @param array $afterUpload
     * @param array $beforeUpload
     * @return array
     */
    private function getAttachmentsDiff(array $afterUpload, array $beforeUpload = array())
    {
        $diff = $beforeUploadItems = array();

        if (!empty($beforeUpload)) {
            foreach ($beforeUpload as $item) {
                $beforeUploadItems[] = $item->getId();
            }
            foreach ($afterUpload as $index=>$item) {
                if (!in_array($item->getId(), $beforeUploadItems)) {
                    $diff[] = $afterUpload[$index];
                }
            }
        } else {
            $diff = $afterUpload;
        }

        return $diff;
    }

    private function getFileDownloadResponse(AttachmentDto $attachmentDto)
    {
        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($attachmentDto->getFilePath());
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $attachmentDto->getFileName(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $attachmentDto->getFileName())
        );

        return $response;
    }
}
