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
use Diamante\UserBundle\Entity\DiamanteUser;
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
            if ($command->branch->getId() !== $ticket->getBranch()->getId()){
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

            if(is_string($command->watcher)) {
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
     *      "/watcher/ticket/{ticket}/{user}",
     *      name="diamante_remove_watcher",
     *      requirements={"ticket"="\d+"}
     * )
     *
     * @param Ticket $ticket
     * @param string $user
     * @return array|Response
     */
    public function deleteWatcherAction(Ticket $ticket, $user)
    {
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
     * @param Ticket $ticket
     * @return RedirectResponse
     */
    public function watchAction(Ticket $ticket)
    {
        $watcherService = $this->get('diamante.ticket.watcher_list.service');

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
     * @param Ticket $ticket
     * @return RedirectResponse
     */
    public function unwatchAction(Ticket $ticket)
    {
        $watcherService = $this->get('diamante.ticket.watcher_list.service');

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

    /**
     * @param string|null $redirectUrl
     * @param array $redirectParams
     * @param bool|true $reload
     * @return array
     */
    private function getWidgetResponse($redirectUrl = null, $redirectParams = [], $reload = true)
    {
        $response = ['reload_page' => $reload];

        if (!is_null($redirectUrl) && !empty($redirectParams)) {
            $response['redirect'] = $this->generateUrl($redirectUrl, $redirectParams);
        }

        return $response;
    }

    /**
     * @return bool
     */
    private function widgetRedirectRequested()
    {
        return !(bool)$this->container->get('request')->get('no_redirect');
    }
}
