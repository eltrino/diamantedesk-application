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

use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\AttachmentInput;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Entity\Comment;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Command\EditCommentCommand;
use Eltrino\DiamanteDeskBundle\Form\Type\CommentType;
use Eltrino\DiamanteDeskBundle\Form\Type\UpdateTicketStatusType;
use Eltrino\DiamanteDeskBundle\Form\CommandFactory;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Command\UpdateStatusCommand;
use Eltrino\DiamanteDeskBundle\Ticket\Api\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("comment")
 */
class CommentController extends Controller
{
    /**
     * @Route(
     *      "/create/{id}",
     *      name="diamante_comment_create",
     *      requirements={"id"="\d+"}
     * )
     *
     * @Template("EltrinoDiamanteDeskBundle:Comment:edit.html.twig")
     *
     * @param Ticket $ticket
     * @return array
     */
    public function createAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
        $command = $this->get('diamante.command_factory')
            ->createEditCommentCommandForCreate($ticket, $this->getUser());
        return $this->edit($command, function($command) {
            $command->attachmentsInput = $this->buildAttachmentsInputDTO($command);
            $this->get('diamante.comment.service')
                ->postNewCommentForTicket($command);
        }, $ticket);
    }

    /**
     * @Route(
     *      "/update/{id}",
     *      name="diamante_comment_update",
     *      requirements={"id"="\d+"}
     * )
     *
     * @Template("EltrinoDiamanteDeskBundle:Comment:edit.html.twig")
     *
     * @param Comment $comment
     * @return array
     */
    public function updateAction($id)
    {
        $comment = $this->get('diamante.comment.service')->loadComment($id);
        $command = $this->get('diamante.command_factory')
            ->createEditCommentCommandForUpdate($comment);
        return $this->edit($command, function($command) use ($comment) {
            $command->attachmentsInput = $this->buildAttachmentsInputDTO($command);
            $this->get('diamante.comment.service')->updateTicketComment($command);
        }, $comment->getTicket());
    }

    /**
     * @param EditCommentCommand $command
     * @return array of AttachmentInput DTOs
     */
    private function buildAttachmentsInputDTO(EditCommentCommand $command)
    {
        $attachmentsInput = array();
        foreach ($command->files as $file) {
            if (!empty($file)) {
                $attachmentsInput[] = AttachmentInput::createFromUploadedFile($file);
            }
        }

        return $attachmentsInput;
    }

    /**
     * @Route("/attachment/list/{id}",
     *      name="diamante_comment_widget_attachment_list",
     *      requirements={"id"="\d+"}
     * )
     * @Template("EltrinoDiamanteDeskBundle:Comment:attachment/list.html.twig")
     */
    public function attachmentList($id)
    {
        $comment = $this->get('diamante.comment.service')->loadComment($id);
        return [
            'comment_id' => $comment->getId(),
            'attachments' => $comment->getAttachments()
        ];
    }

    /**
     * @param EditCommentCommand $command
     * @param $callback
     * @param Ticket $ticket
     * @return array
     */
    private function edit(EditCommentCommand $command, $callback, Ticket $ticket)
    {
        $response = null;
        $form = $this->createForm(new CommentType(), $command);
        $formView = $form->createView();
        $formView->children['files']->vars = array_replace(
            $formView->children['files']->vars,
            array('full_name' => 'diamante_comment_form[files][]')
        );
        try {
            $this->handle($form);
            $callback($command);

            $newStatus = $form->get('ticketStatus')->getData();

            if (false === ($newStatus == $ticket->getStatus()->getValue())) {
                $ticketCommand = new UpdateStatusCommand();
                $ticketCommand->ticketId = $command->ticket;
                $ticketCommand->status = $newStatus;
                $this->get('diamante.ticket.service')->updateStatus($ticketCommand);
            }

            if ($command->id) {
                $this->addSuccessMessage('Comment successfully saved.');
            } else {
                $this->addSuccessMessage('Comment successfully created.');
            }
            $response = $this->getSuccessSaveResponse($ticket->getId());
        } catch (\LogicException $e) {
            $response = array('form' => $formView, 'ticket' => $ticket);
        }
        return $response;
    }

    /**
     * @Route(
     *      "/delete/ticket/{ticketId}/comment/{commentId}",
     *      name="diamante_comment_delete",
     *      requirements={"ticketId"="\d+", "commentId"="\d+"}
     * )
     *
     * @param Comment $comment
     * @return Response
     */
    public function deleteAction($ticketId, $commentId)
    {
        try {
            $this->get('diamante.comment.service')
                ->deleteTicketComment($commentId);

            $this->addSuccessMessage('Comment successfully deleted.');
        } catch (Exception $e) {
            $this->addErrorMessage($e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('diamante_ticket_view', array('id' => $ticketId))
        );
    }

    /**
     * @Route(
     *      "/attachment/download/comment/{commentId}/attachment/{attachId}",
     *      name="diamante_ticket_comment_attachment_download",
     *      requirements={"commentId"="\d+", "attachId"="\d+"}
     * )
     * @return Reponse
     * @todo move to application service
     */
    public function downloadAttachmentAction($commentId, $attachId)
    {
        /** @var CommentService $commentService */
        $commentService = $this->get('diamante.comment.service');
        $attachment = $commentService->getCommentAttachment($commentId, $attachId);

        $filename = $attachment->getFilename();
        $filePathname = realpath($this->container->getParameter('kernel.root_dir').'/attachment')
            . '/' . $attachment->getFilename();

        if (!file_exists($filePathname)) {
            throw $this->createNotFoundException('Attachment not found');
        }

        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($filePathname);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            iconv('UTF-8', 'ASCII//TRANSLIT', $filename)
        );

        return $response;
    }

    /**
     * @Route(
     *      "/attachment/remove/comment/{commentId}/attachment/{attachId}",
     *      name="diamante_ticket_comment_attachment_remove",
     *      requirements={"commentId"="\d+", "attachId"="\d+"}
     * )
     * @Template
     *
     * @param integer $commentId
     * @param integer $attachId
     */
    public function removeAttachmentAction($commentId, $attachId)
    {
        /** @var CommentService $commentService */
        $commentService = $this->get('diamante.comment.service');
        $commentService->removeAttachmentFromComment($commentId, $attachId);
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('Attachment successfully deleted.')
        );
        $response = $this->redirect($this->generateUrl(
            'diamante_comment_update',
            array('id' => $commentId)
        ));
        return $response;
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

    /**
     * @param $message
     */
    private function addErrorMessage($message)
    {
        $this->get('session')->getFlashBag()->add(
            'error',
            $this->get('translator')->trans($message)
        );
    }

    /**
     * @param int $ticketId
     * @return array
     */
    private function getSuccessSaveResponse($ticketId)
    {
        return $this->get('oro_ui.router')->redirectAfterSave(
            ['route' => 'diamante_comment_update', 'parameters' => ['id' => $ticketId]],
            ['route' => 'diamante_ticket_view', 'parameters' => ['id' => $ticketId]]
        );
    }

}
