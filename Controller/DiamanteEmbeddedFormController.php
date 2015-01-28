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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;

use Diamante\DeskBundle\Model\User\User;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\EmbeddedFormBundle\Api\Command\EmbeddedTicketCommand;
use Diamante\EmbeddedFormBundle\Form\Type\DiamanteEmbeddedFormType;

use Symfony\Component\Form\Form;
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

        $command = new EmbeddedTicketCommand();

        $formType = new DiamanteEmbeddedFormType();
        $form = $this->createForm($formType, $command);
        $formView = $form->createView();

        $formView->children['attachmentsInput']->vars = array_replace(
            $formView->children['attachmentsInput']->vars,
            array('full_name' => 'diamante_embedded_form[attachmentsInput][]')
        );

        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {

            $data = $this->getRequest()->get('diamante_embedded_form');

            //Initialize Reporter
            $diamanteUserRepository = $this->get('diamante.user.repository');
            $diamanteUser = $diamanteUserRepository->findUserByEmail($data['emailAddress']);
            if (is_null($diamanteUser)) {
                $diamanteUser = $this->get('diamante.user_factory')->create($data['emailAddress'], $data['emailAddress'], $data['firstName'], $data['lastName']);
                $diamanteUserRepository->store($diamanteUser);
            }
            $reporterId = $diamanteUser->getId();
            $reporter = new User($reporterId, User::TYPE_DIAMANTE);

            //Set Command for embedded form
            $command->reporter = $reporter;
            $command->priority = Priority::PRIORITY_MEDIUM;
            $command->source = Source::WEB;
            $command->status = Status::NEW_ONE;
            $command->branch = $formEntity->getBranch();
            $command->assignee = null;
            if ($formEntity->getBranch()) {
                $assignee = $formEntity->getBranch()->getDefaultAssignee();
            }
            if ($assignee) {
                $command->assignee = $assignee;
            }

            $form->handleRequest($this->getRequest());

            if ($form->isValid()) {

                $command->branch = $formEntity->getBranch()->getId();

                $this->get('diamante.ticket.service')->createTicket($command);

                return $this->redirect($this->generateUrl('oro_embedded_form_success', ['id' => $formEntity->getId()]));
            }

        }

        $this->render(
            'OroEmbeddedFormBundle:EmbedForm:form.html.twig',
            [
                'form'             => $formView,
                'formEntity'       => $formEntity,
                'customFormLayout' => $formType->getFormLayout()
            ],
            $response
        );

        return $response;
    }


}