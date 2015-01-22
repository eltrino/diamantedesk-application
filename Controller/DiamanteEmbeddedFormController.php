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
use Oro\Bundle\EmbeddedFormBundle\Manager\EmbeddedFormManager;

use Diamante\DeskBundle\Model\User\User;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\EmbeddedFormBundle\Api\Command\EmbeddedTicketCommand;

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

        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {

            $data = $this->getRequest()->get('diamante_embedded_form');

            //Initialize Reporter
            $apiUserRepository = $this->get('diamante.api.user.repository');
            $apiUser = $apiUserRepository->findUserByEmail($data['emailAddress']);
            if (is_null($apiUser)) {
                $apiUser = $this->get('diamante.api.user.entity.factory')->create($data['emailAddress'], $data['emailAddress'], $data['firstName'], $data['lastName']);
                $apiUserRepository->store($apiUser);
            }
            $reporterId = $apiUser->getId();
            $reporter = new User($reporterId, User::TYPE_DIAMANTE);

            //Set Command for embedded form
            $command = new EmbeddedTicketCommand();
            $command->reporter = $reporter;
            $command->priority = Priority::PRIORITY_MEDIUM;
            $command->source = Source::WEB;
            $command->status = Status::NEW_ONE;
            $command->branch = $formEntity->getBranch();
            if ($formEntity->getBranch()->getDefaultAssignee()) {
                $command->assignee = $formEntity->getBranch()->getDefaultAssignee();
            } else {
                $command->assignee = null;
            }

            /** @var EmbeddedFormManager $formManager */
            $formManager = $this->get('oro_embedded_form.manager');
            $form = $formManager->createForm($formEntity->getFormType(), $command);
            $formView = $form->createView();

            $form->handleRequest($this->getRequest());

            if ($form->isValid()) {

                $command->branch = $formEntity->getBranch()->getId();

                $this->get('diamante.ticket.service')->createTicket($command);

                return $this->redirect($this->generateUrl('oro_embedded_form_success', ['id' => $formEntity->getId()]));
            }

            return $this->redirect($this->generateUrl('oro_embedded_form_submit', ['id' => $formEntity->getId()]));
        }

        return $response;
    }


}