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

use Diamante\EmbeddedFormBundle\Form\Extension\EmbeddedFormTypeExtension;
use Diamante\EmbeddedFormBundle\Form\Type\DiamanteEmbeddedFormType;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\EmbeddedFormBundle\Manager\EmbeddedFormManager;
use Oro\Bundle\EmbeddedFormBundle\Manager\EmbedFormLayoutManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;

use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\EmbeddedFormBundle\Api\Command\EmbeddedTicketCommand;

class DiamanteEmbeddedFormController extends Controller
{
    /**
     * @Route(
     *      "/submit-ticket/{id}",
     *      name="diamante_embedded_form_submit",
     *      requirements={"id"="[-\d\w]+"},
     * )
     */
    public function formAction(Request $request, EmbeddedForm $formEntity)
    {
        $response = new Response();
        $response->setPublic();
        $formEntity->setFormType(DiamanteEmbeddedFormType::class);
        //$response->setEtag($formEntity->getId() . $formEntity->getUpdatedAt()->format(\DateTime::ISO8601));
        if ($response->isNotModified($request)) {
            return $response;
        }

        $command = new EmbeddedTicketCommand();
        $formManager = $this->get('oro_embedded_form.manager');
        $form        = $formManager->createForm($formEntity->getFormType(), $command);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {

            $data = $request->get('diamante_embedded_form');

            //Initialize Reporter
            $diamanteUserRepository = $this->get('diamante.user.repository');
            $diamanteUser = $diamanteUserRepository->findUserByEmail($data['emailAddress']);
            if (is_null($diamanteUser)) {
                $diamanteUser = $this->get('diamante.user_factory')->create($data['emailAddress'], $data['firstName'], $data['lastName']);
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
            $command->subject = $data['subject'];
            $command->description = $data['description'];
            if ($formEntity->getBranch() && $formEntity->getBranch()->getDefaultAssignee()) {
                $assignee = $formEntity->getBranch()->getDefaultAssignee();
            } else {
                $assignee = null;
            }
            $command->assignee = $assignee;

            $form->handleRequest($request);

            if ($form->isValid()) {

                $command->branch = $formEntity->getBranch()->getId();

                $this->get('diamante.ticket.service')->createTicket($command);

                return $this->redirect($this->generateUrl('oro_embedded_form_success', ['id' => $formEntity->getId()]));
            }

        }

        $formView = $form->createView();

        $formView->children['attachmentsInput']->vars = array_replace(
            $formView->children['attachmentsInput']->vars,
            array('full_name' => 'diamante_embedded_form[attachmentsInput][]')
        );


        // TODO: Next code should be refactored.
        // TODO: Should be changed due to new EmbeddedFormBundle requirements
        $formResponse = $this->render(
            'DiamanteEmbeddedFormBundle::embeddedForm.html.twig',
            [
                'form'             => $formView,
                'formEntity'       => $formEntity
            ]
        );

        $layoutManager = $this->get('oro_embedded_form.embed_form_layout_manager');
        $layout = $layoutManager->getLayout($formEntity, $form);
        $layoutContent = $layout->render();

        $replaceString = '<div id="page">';

        $response->setContent(
            str_replace($replaceString, $replaceString . $formResponse->getContent(), $layoutContent)
        );

        return $response;
    }


}