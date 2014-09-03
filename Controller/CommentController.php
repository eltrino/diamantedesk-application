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
use Eltrino\DiamanteDeskBundle\Form\Command\EditCommentCommand;
use Eltrino\DiamanteDeskBundle\Form\Type\CommentType;
use Eltrino\DiamanteDeskBundle\Form\Type\UpdateTicketStatusType;
use Eltrino\DiamanteDeskBundle\Form\CommandFactory;
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
    public function createAction(Ticket $ticket)
    {
        $command = $this->get('diamante.command_factory')
            ->createEditCommentCommandForCreate($ticket, $this->getUser());
        return $this->edit($command, function($command) {
            $this->get('diamante.comment.service')
                ->postNewCommentForTicket(
                    $command->content,
                    $command->ticket->getId(),
                    $command->author->getId(),
                    $this->buildAttachmentsInputDTO($command)
                );
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
    public function updateAction(Comment $comment)
    {
        $command = $this->get('diamante.command_factory')
            ->createEditCommentCommandForUpdate($comment);
        return $this->edit($command, function($command) use ($comment) {
            $this->get('diamante.comment.service')
                ->updateTicketComment($comment->getId(), $command->content, $this->buildAttachmentsInputDTO($command));
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
    public function attachmentList(Comment $comment)
    {
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
                $this->get('diamante.ticket.service')
                    ->updateStatus(
                        $command->ticket->getId(),
                        $newStatus
                    );
            }

            if ($command->id) {
                $this->addSuccessMessage('Comment successfully saved.');
            } else {
                $this->addSuccessMessage('Comment successfully created.');
            }
            $response = $this->getSuccessSaveResponse($ticket);
        } catch (\LogicException $e) {
            $response = array('form' => $formView);
        }
        return $response;
    }

    /**
     * @Route(
     *      "/delete/{id}",
     *      name="diamante_comment_delete",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param Comment $comment
     * @return Response
     */
    public function deleteAction(Comment $comment)
    {
        try {
            $this->get('diamante.comment.service')
                ->deleteTicketComment($comment->getId());

            $this->addSuccessMessage('Comment successfully deleted.');
        } catch (Exception $e) {
            $this->addErrorMessage($e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('diamante_ticket_view', array(
                    'id' => $comment->getTicket()->getId())
            )
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
     *
     * @param integer $commentId
     * @param integer $attachId
     * @Template
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
     * @param Ticket $ticket
     * @return array
     */
    private function getSuccessSaveResponse(Ticket $ticket)
    {
        return $this->get('oro_ui.router')->redirectAfterSave(
            ['route' => 'diamante_comment_update', 'parameters' => ['id' => $ticket->getId()]],
            ['route' => 'diamante_ticket_view', 'parameters' => ['id' => $ticket->getId()]]
        );
    }

}
