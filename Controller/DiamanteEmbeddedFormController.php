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
namespace Diamante\EmbeddedFormBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;

use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitAfterEvent;
use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitBeforeEvent;
use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;
use Oro\Bundle\EmbeddedFormBundle\Manager\EmbeddedFormManager;

use Diamante\ApiBundle\Model\ApiUser\ApiUserFactory;
use Diamante\ApiBundle\Model\ApiUser\ApiUserRepository;
use Diamante\DeskBundle\Model\User\User;

//use Diamante\DeskBundle\Api\Dto\AttachmentInput;
//use Diamante\DeskBundle\Model\Ticket\Ticket;
//use Diamante\DeskBundle\Form\Type\EmbeddedFormType;
//use Diamante\DeskBundle\Model\User\User;
//use Diamante\DeskBundle\Api\TicketService;
//
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//
//use Diamante\DeskBundle\Entity\Branch;
use Symfony\Component\Form\Form;
//use Symfony\Component\Form\FormFactory;
//use Symfony\Component\HttpFoundation\BinaryFileResponse;
//use Symfony\Component\HttpFoundation\RedirectResponse;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpFoundation\JsonResponse;
//
//use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
//use Diamante\DeskBundle\Api\Dto\AttachmentDto;
//use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @Route("diamante-embedded-form")
 */
class DiamanteEmbeddedFormController extends Controller
{
    /**
     * @Route(
     *      "/submit/{id}",
     *      name="diamante_embedded_form_submit",
     *      requirements={"id"="[-\d\w]+"},
     * )
     */
    public function formAction(EmbeddedForm $formEntity)
    {
        $response = new Response();
        $response->setPublic();
        //$response->setEtag($formEntity->getId() . $formEntity->getUpdatedAt()->format(\DateTime::ISO8601));
        if ($response->isNotModified($this->getRequest())) {
            return $response;
        }

        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {

            $data = $this->getRequest()->get('diamante_embedded_form');

            $branch = null;
            if (isset($data['branch'])) {
                $branch = $this->get('diamante.branch.repository')->get($data['branch']);
                if (is_null($branch)) {
                    throw new \RuntimeException('Branch loading failed. Branch not found.');
                }
            }

            $apiUserRepository = $this->get('diamante.api.user.repository');
            $apiUser = $apiUserRepository->findUserByEmail($data['emailAddress']);
            if (is_null($apiUser)) {
                $apiUser = $this->get('diamante.api.user.entity.factory')->create($data['emailAddress'], $data['emailAddress'], $data['firstName'], $data['lastName']);
                $apiUserRepository->store($apiUser);
            }
            $reporterId = $apiUser->getId();
            $reporter = new User($reporterId, User::TYPE_DIAMANTE);

            $command = $this->get('diamante.command_factory')
                ->createEmbeddedFormCommand($branch, $reporter);

            /** @var EmbeddedFormManager $formManager */
            $formManager = $this->get('oro_embedded_form.manager');
            $form = $formManager->createForm($formEntity->getFormType(), $command);
            $formView = $form->createView();
            $formView->children['files']->vars = array_replace($formView->children['files']->vars, array('full_name' => 'diamante_embedded_form[files][]'));

            $response = null;

            try {
                $this->handle($form);

                $command->branch = $command->branch->getId();
                $command->assignee = 1;

                $attachments = array();
                foreach ($command->files as $file) {
                    if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                        array_push($attachments, AttachmentInput::createFromUploadedFile($file));
                    }
                }
                $command->attachmentsInput = $attachments;

                $this->get('diamante.ticket.service')->createTicket($command);

                return $this->redirect($this->generateUrl('oro_embedded_form_success', ['id' => $formEntity->getId()]));
            } catch (MethodNotAllowedException $e) {
                $response = array('form' => $formView);
            } catch (\Exception $e) {
                echo $e->getMessage();dd();
                $this->addErrorMessage('diamante.desk.ticket.messages.create.error');
                $response = array('form' => $formView);
            }
            return $response;
        }

        return $response;
//        $branch = null;
//        if ($id) {
//            $branch = $this->get('diamante.branch.service')->getBranch($id);
//        }
//        $command = $this->get('diamante.command_factory')
//            ->createCreateTicketCommand($branch, new User($this->getUser()->getId(), User::TYPE_ORO));
//
//        $response = null;
//        $form = $this->createForm(new CreateTicketType(), $command);
//        $formView = $form->createView();
//        $formView->children['files']->vars = array_replace($formView->children['files']->vars, array('full_name' => 'diamante_ticket_form[files][]'));
//        try {
//            $this->handle($form);
//
//            $command->branch = $command->branch->getId();
//            $command->assignee = $command->assignee ? $command->assignee->getId() : null;
//
//            $attachments = array();
//            foreach ($command->files as $file) {
//                if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
//                    array_push($attachments, AttachmentInput::createFromUploadedFile($file));
//                }
//            }
//            $command->attachmentsInput = $attachments;
//
//            $ticket = $this->get('diamante.ticket.service')->createTicket($command);
//
//            $this->addSuccessMessage('diamante.desk.ticket.messages.create.success');
//            $response = $this->getSuccessSaveResponse($ticket);
//        } catch (MethodNotAllowedException $e) {
//            $response = array('form' => $formView);
//        } catch (\Exception $e) {
//            $this->addErrorMessage('diamante.desk.ticket.messages.create.error');
//            $response = array('form' => $formView);
//        }
//        return $response;
    }

    /**
     * @param Form $form
     * @throws MethodNotAllowedException
     * @throws ValidatorException
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
}