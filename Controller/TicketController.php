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

use Diamante\DeskBundle\Api\Dto\AttachmentDto;
use Diamante\DeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\TicketService;
use Diamante\DeskBundle\Entity\Attachment;
use Diamante\DeskBundle\Form\CommandFactory;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketNotFoundException;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketMovedException;
use Diamante\UserBundle\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("tickets")
 */
class TicketController extends Controller
{
    use Shared\FormHandlerTrait;
    use Shared\ExceptionHandlerTrait;
    use Shared\SessionFlashMessengerTrait;
    use Shared\ResponseHandlerTrait;

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
        $linksList = $link = array();
        foreach($filtersList as $filter) {
            $link['name'] =  $filter->getName();
            $link['url'] = $filtersGenerator->generateGridFilterUrl($filter->getId());
            $linksList[] = $link;
        }

        return ['linksList' => $linksList];
    }

    /**
     * @Route(
     *      "/view/{key}",
     *      name="diamante_ticket_view",
     *      requirements={"key"=".*-\d+"}
     * )
     * @Template
     *
     * @param string $key
     * @return array|Response
     */
    public function viewAction($key)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicketByKey($key);

            return ['entity'  => $ticket, 'ticketKey' => (string)$ticket->getKey()];
        } catch (TicketMovedException $e) {
            return $this->redirect(
                $this->generateUrl('diamante_ticket_view', array('key' => $e->getTicketKey())
                )
            );
        } catch (\Exception $e) {
            $this->handleException($e);
            throw $this->createNotFoundException($e->getMessage(), $e);
        }
    }

    /**
     * @Route(
     *      "/watcher/ticket/{ticketId}/{user}",
     *      name="diamante_remove_watcher",
     *      requirements={"ticketId"="\d+"}
     * )
     *
     * @param int $ticketId
     * @param string $user
     * @return array|Response
     */
    public function deleteWatcherAction($ticketId, $user)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('DiamanteDeskBundle:Ticket');
        $ticket = $repository->get($ticketId);
        $ticketKey = $ticket->getKey();

        try {
            $user = User::fromString($user);
            $this->get('diamante.watcher.service.api')->removeWatcher($ticket, $user);
            $this->addSuccessMessage('diamante.desk.ticket.messages.watcher_remove.success');
            $response = $this->redirect($this->generateUrl(
                'diamante_ticket_view',
                array('key' => $ticketKey)
            ));
        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->redirect($this->generateUrl(
                'diamante_ticket_list'
            ));
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->redirect($this->generateUrl(
                'diamante_ticket_view',
                array('key' => $ticketKey)
            ));
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
     * @param int|null $id
     * @return array
     */
    public function createAction($id = null)
    {
        $branch = null;
        if (!is_null($id)) {
            $branch = $this->get('diamante.branch.service')->getBranch($id);
        }
        $command = $this->get('diamante.command_factory')
            ->createCreateTicketCommand($branch, new User($this->getUser()->getId(), User::TYPE_ORO));

        $response = null;
        $form = $this->createForm('diamante_ticket_form', $command);
        $formView = $form->createView();
        $formView->children['attachmentsInput']->vars = array_replace(
            $formView->children['attachmentsInput']->vars,
            array('full_name' => 'diamante_ticket_form[attachmentsInput][]')
        );
        try {
            $this->handle($form);

            $branchAssignee = $command->branch->getDefaultAssignee();
            if ($command->assignee) {
                $command->assignee = $command->assignee->getId();
            } elseif ($branchAssignee) {
                $command->assignee = $branchAssignee->getId();
            }

            $command->branch = $command->branch->getId();

            $ticket = $this->get('diamante.ticket.service')->createTicket($command);

            $this->addSuccessMessage('diamante.desk.ticket.messages.create.success');
            $response = $this->getSuccessSaveResponse('diamante_ticket_update', 'diamante_ticket_view', ['key' => (string)$ticket->getKey()]);
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = array('form' => $formView);
        }
         return $response;
    }

    /**
     * @Route(
     *      "/update/{key}",
     *      name="diamante_ticket_update",
     *      requirements={"key"=".*-\d+"}
     * )
     *
     * @Template("DiamanteDeskBundle:Ticket:update.html.twig")
     *
     * @param string $key
     * @return array
     */
    public function updateAction($key)
    {
        try {
            $ticket = $this->get('diamante.ticket.service')->loadTicketByKey($key);

            $command = $this->get('diamante.command_factory')
                ->createUpdateTicketCommand($ticket);
            $response = null;
            $form = $this->createForm('diamante_ticket_update_form', $command);

            $formView = $form->createView();
            $formView->children['attachmentsInput']->vars = array_replace(
                $formView->children['attachmentsInput']->vars,
                array('full_name' => 'diamante_ticket_form[attachmentsInput][]')
            );
            $this->handle($form);

            $command->assignee = $command->assignee ? $command->assignee->getId() : null;

            $ticket = $this->get('diamante.ticket.service')->updateTicket($command);
            $this->addSuccessMessage('diamante.desk.ticket.messages.save.success');
            $response = $this->getSuccessSaveResponse('diamante_ticket_update', 'diamante_ticket_view', ['key' => (string)$ticket->getKey()]);
        } catch (TicketMovedException $e) {
            return $this->redirect(
                $this->generateUrl(
                    'diamante_ticket_update',
                    array('key' => $e->getTicketKey())
                )
            );
        } catch (\Exception $e) {
            $this->handleException($e);

            $response = array(
                'form' => $formView,
                'branchId' => $ticket->getBranch()->getId(),
                'branchName' => $ticket->getBranch()->getName(),
                'branchLogoPathname' => $ticket->getBranch()->getLogo() ? $ticket->getBranch()->getLogo()->getPathname() : null
            );
        }
        return $response;
    }

    /**
     * @Route(
     *      "/delete/{key}",
     *      name="diamante_ticket_delete",
     *      requirements={"key"=".*-\d+"}
     * )
     *
     * @param string $key
     * @return Response
     */
    public function deleteAction($key)
    {
        try {
            $this->get('diamante.ticket.service')->deleteTicketByKey($key);
            return new Response(null, 204);
        } catch (\Exception $e) {
            $this->handleException($e);
            return new Response($this->get('translator')->trans('diamante.desk.ticket.messages.delete.error'), 500);
        }
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
        $form = $this->createForm('diamante_attachment_form', $commandFactory->createAddTicketAttachmentCommand($ticket));
        $formView = $form->createView();
        $formView->children['attachmentsInput']->vars = array_replace(
            $formView->children['attachmentsInput']->vars,
            array('full_name' => 'diamante_attachment_form[attachmentsInput][]')
        );
        return array('ticket' => $ticket, 'form' => $formView);
    }

    /**
     * @Route(
     *      "/attachPost/ticket/{id}",
     *      name="diamante_ticket_create_attach_post",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     * @return Response
     */
    public function attachPostAction($id)
    {
        $ticketService = $this->get('diamante.ticket.service');
        $ticket = $ticketService->loadTicket($id);
        $response = null;
        $commandFactory = new CommandFactory();
        $form = $this->createForm('diamante_attachment_form', $commandFactory->createAddTicketAttachmentCommand($ticket));
        $formView = $form->createView();
        $formView->children['attachmentsInput']->vars = array_replace(
            $formView->children['attachmentsInput']->vars,
            array('full_name' => 'diamante_attachment_form[attachmentsInput][]')
        );

        try {
            $this->handle($form);
            $command = $form->getData();
            $uploadedAttachments = $ticketService->addAttachmentsForTicket($command);
            $this->addSuccessMessage('diamante.desk.attachment.messages.create.success');

            if ($this->container->get('request')->request->get('diam-dropzone')) {
                $response = $this->prepareDropzoneAttachmentsResponse($id, $uploadedAttachments);
            } else {
                $response = $this->get('oro_ui.router')->redirectAfterSave(
                    ['route' => 'diamante_attachment_attach', 'parameters' => []],
                    ['route' => 'diamante_ticket_view', 'parameters' => ['key' => (string) $ticket->getKey()]]
                );
            }
        } catch (\Exception $e) {
            $this->handleException($e);
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
     * @param int $ticketId
     * @param int $attachId
     * @return RedirectResponse
     */
    public function removeAttachmentAction($ticketId, $attachId)
    {
        /** @var TicketService $ticketService */
        $ticketService = $this->get('diamante.ticket.service');
        $removeTicketAttachment = new RemoveTicketAttachmentCommand();
        $removeTicketAttachment->ticketId     = $ticketId;
        $removeTicketAttachment->attachmentId = $attachId;

        try {
            $ticketKey = $ticketService->removeAttachmentFromTicket($removeTicketAttachment);
            $this->addSuccessMessage('diamante.desk.attachment.messages.delete.success');
            $response = $this->redirect($this->generateUrl(
                'diamante_ticket_view',
                array('key' => $ticketKey)
            ));
        } catch (TicketNotFoundException $e) {
            $this->handleException($e);
            $response = $this->redirect($this->generateUrl(
                'diamante_ticket_list'
            ));
        } catch (\Exception $e) {
            $this->handleException($e);

            $ticketKey = $ticketService->loadTicket($ticketId)->getKey();
            $response = $this->redirect($this->generateUrl(
                'diamante_ticket_view',
                array('key' => $ticketKey)
            ));
        }

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
        $retrieveTicketAttachmentCommand = new RetrieveTicketAttachmentCommand();
        $retrieveTicketAttachmentCommand->ticketId = $ticketId;
        $retrieveTicketAttachmentCommand->attachmentId = $attachId;
        try {
            $attachment = $this->get('diamante.ticket.service')->getTicketAttachment($retrieveTicketAttachmentCommand);
            $attachmentDto = AttachmentDto::createFromAttachment($attachment);
            $response = $this->getFileDownloadResponse($attachmentDto);

            return $response;
        } catch (\Exception $e) {
            $this->handleException($e);
            throw $this->createNotFoundException('Attachment not found');
        }
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
    public function attachmentListAction($id)
    {
        $ticket = $this->get('diamante.ticket.service')->loadTicket($id);
        return [
            'ticket' => $ticket,
            'attachments' => $ticket->getAttachments()
        ];
    }

    /**
     * @Route(
     *       "/watch/ticket/{ticket}",
     *      name="diamante_ticket_watch",
     *      requirements={"ticket"="\d+"}
     * )
     *
     * @param int $ticket
     * @return RedirectResponse
     */
    public function watchAction($ticket)
    {
        $watcherService = $this->get('diamante.ticket.watcher_list.service');

        $ticket = $this->get('diamante.ticket.service')->loadTicket($ticket);
        $user = new User($this->getUser()->getId(), User::TYPE_ORO);

        $watcherService->addWatcher($ticket, $user);

        $this->addSuccessMessage('diamante.desk.watcher.messages.watch.success');
        $response = $this->redirect($this->generateUrl(
            'diamante_ticket_view',
            array('key' => $ticket->getKey())
        ));

        return $response;
    }

    /**
     * @Route(
     *       "/unwatch/ticket/{ticket}",
     *      name="diamante_ticket_unwatch",
     *      requirements={"ticket"="\d+"}
     * )
     *
     * @param int $ticket
     * @return RedirectResponse
     */
    public function unwatchAction($ticket)
    {
        $watcherService = $this->get('diamante.ticket.watcher_list.service');

        $ticket = $this->get('diamante.ticket.service')->loadTicket($ticket);
        $user = new User($this->getUser()->getId(), User::TYPE_ORO);

        $watcherService->removeWatcher($ticket, $user);

        $this->addSuccessMessage('diamante.desk.watcher.messages.unwatch.success');
        $response = $this->redirect($this->generateUrl(
            'diamante_ticket_view',
            array('key' => $ticket->getKey())
        ));

        return $response;
    }

    /**
     * Get attachments list as array ready for conversion to JSON
     *
     * @param int $ticketId
     * @param Attachment[] $attachments
     * @return array
     */
    private function prepareDropzoneAttachmentsResponse($ticketId, $attachments)
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
                'hash'     => $attachment->getHash(),
            );
        }
        $data["staticFlashMessages"] = $this->get('session')->getFlashBag()->all();

        $response = new JsonResponse();
        $response->setData($data);
        $response->setStatusCode(201);

        return $response;
    }
}
