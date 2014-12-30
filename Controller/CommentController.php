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

use Diamante\DeskBundle\Api\Command\RemoveCommentAttachmentCommand;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Entity\Comment;
use Diamante\DeskBundle\Api\Command\CommentCommand;
use Diamante\DeskBundle\Form\Type\CommentType;
use Diamante\DeskBundle\Form\Type\UpdateTicketStatusType;
use Diamante\DeskBundle\Form\CommandFactory;
use Diamante\DeskBundle\Api\Command\UpdateStatusCommand;
use Diamante\DeskBundle\Api\CommentService;
use Diamante\DeskBundle\Model\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;

use Diamante\DeskBundle\Api\Command\RetrieveCommentAttachmentCommand;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Exception\ValidatorException;

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
     * @Template("DiamanteDeskBundle:Comment:edit.html.twig")
     *
     * @param Ticket $id
     * @return array
     */
    public function createAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
        $command = $this->get('diamante.command_factory')
            ->createCommentCommandForCreate($ticket, new User($this->getUser()->getId(), User::TYPE_ORO));
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
     * @Template("DiamanteDeskBundle:Comment:edit.html.twig")
     *
     * @param int $id
     * @return array
     */
    public function updateAction($id)
    {
        $comment = $this->get('diamante.comment.service')->loadComment($id);
        $command = $this->get('diamante.command_factory')
            ->createCommentCommandForUpdate($comment);
        return $this->edit($command, function($command) use ($comment) {
            $command->attachmentsInput = $this->buildAttachmentsInputDTO($command);
            $this->get('diamante.comment.service')->updateTicketComment($command);
        }, $comment->getTicket());
    }

    /**
     * @param CommentCommand $command
     * @return array of AttachmentInput DTOs
     */
    private function buildAttachmentsInputDTO(CommentCommand $command)
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
     * @Template("DiamanteDeskBundle:Comment:attachment/list.html.twig")
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
     * @param CommentCommand $command
     * @param $callback
     * @param Ticket $ticket
     * @return array
     */
    private function edit(CommentCommand $command, $callback, Ticket $ticket)
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

            if ($command->id) {
                $this->addSuccessMessage('diamante.desk.comment.messages.save.success');
            } else {
                $this->addSuccessMessage('diamante.desk.comment.messages.create.success');
            }
            $response = $this->getSuccessSaveResponse((string) $ticket->getKey());
        } catch (MethodNotAllowedException $e) {
            $response = array('form' => $formView, 'ticket' => $ticket);
        } catch (\Exception $e) {
            $this->addErrorMessage('diamante.desk.comment.messages.create.error');
            $response = array('form' => $formView, 'ticket' => $ticket);
        }
        return $response;
    }

    /**
     * @Route(
     *      "/delete/ticket/{ticketKey}/comment/{commentId}",
     *      name="diamante_comment_delete",
     *      requirements={"ticketKey"="[A-Z]+-\d+", "commentId"="\d+"}
     * )
     *
     * @param string $ticketKey
     * @param int $commentId
     * @return Response
     */
    public function deleteAction($ticketKey, $commentId)
    {
        try {
            $this->get('diamante.comment.service')->deleteTicketComment($commentId);

            $this->addSuccessMessage('diamante.desk.comment.messages.delete.success');
        } catch (Exception $e) {
            //TODO: Log original error
            $this->addErrorMessage('diamante.desk.comment.messages.delete.error');
        }

        return $this->redirect(
            $this->generateUrl('diamante_ticket_view', array('key' => $ticketKey))
        );
    }

    /**
     * @Route(
     *      "/attachment/download/comment/{commentId}/attachment/{attachId}",
     *      name="diamante_ticket_comment_attachment_download",
     *      requirements={"commentId"="\d+", "attachId"="\d+"}
     * )
     * @return Response
     * @todo move to application service
     */
    public function downloadAttachmentAction($commentId, $attachId)
    {
        /** @var CommentService $commentService */
        $commentService = $this->get('diamante.comment.service');
        $retrieveCommentAttachment = new RetrieveCommentAttachmentCommand();
        $retrieveCommentAttachment->attachmentId = $attachId;
        $retrieveCommentAttachment->commentId = $commentId;
        $attachment = $commentService->getCommentAttachment($retrieveCommentAttachment);

        $filename = $attachment->getFilename();
        $filePathname = realpath($this->container->getParameter('kernel.root_dir') . '/attachments/comment')
            . '/' . $attachment->getFilename();

        if (!file_exists($filePathname)) {
            $this->addErrorMessage('diamante.desk.attachment.messages.get.error');
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
     * @return Response
     */
    public function removeAttachmentAction($commentId, $attachId)
    {
        /** @var CommentService $commentService */
        $commentService = $this->get('diamante.comment.service');

        $removeCommentAttachmentCommand = new RemoveCommentAttachmentCommand();
        $removeCommentAttachmentCommand->commentId = $commentId;
        $removeCommentAttachmentCommand->attachmentId = $attachId;

        try {
            $commentService->removeAttachmentFromComment($removeCommentAttachmentCommand);
            $this->addSuccessMessage('diamante.desk.attachment.messages.delete.success');
        } catch (Exception $e) {
            //TODO: Log original error
            $this->addErrorMessage('diamante.desk.attachment.messages.delete.error');
        }

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
            throw new MethodNotAllowedException(array('POST'),'Form can be posted only by "POST" method.');
        }

        $form->handleRequest($this->getRequest());

        if (false === $form->isValid()) {
            throw new ValidatorException('Form object validation failed, form is invalid.');
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
     * @param string $ticketKey
     * @return array
     */
    private function getSuccessSaveResponse($ticketKey)
    {
        return $this->get('oro_ui.router')->redirectAfterSave(
            ['route' => 'diamante_comment_update', 'parameters' => ['key' => $ticketKey]],
            ['route' => 'diamante_ticket_view', 'parameters' => ['key' => $ticketKey]]
        );
    }

}
