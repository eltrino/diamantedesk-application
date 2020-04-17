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
use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitAfterEvent;
use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitBeforeEvent;
use Oro\Bundle\EmbeddedFormBundle\Manager\EmbeddedFormManager;
use Oro\Bundle\EmbeddedFormBundle\Manager\EmbedFormLayoutManager;
use Symfony\Component\Form\FormInterface;
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
     * @param EmbeddedForm $formEntity
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function formAction(EmbeddedForm $formEntity, Request $request)
    {
        $response = new Response();
        $response->setPublic();
        // $formEntity->setFormType(DiamanteEmbeddedFormType::class);
        //$response->setEtag($formEntity->getId() . $formEntity->getUpdatedAt()->format(\DateTime::ISO8601));
        if ($response->isNotModified($request)) {
            return $response;
        }

        $isInline = $request->query->getBoolean('inline');

        $command = new EmbeddedTicketCommand();

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var EmbeddedFormManager $formManager */
        $formManager = $this->get('oro_embedded_form.manager');
        $type = $formEntity->getFormType();
        $type1 = DiamanteEmbeddedFormType::class;
        $form        = $formManager->createForm(DiamanteEmbeddedFormType::class, $command);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $data = $request->get('diamante_embedded_form');
            $dataClass = $form->getConfig()->getOption('data_class');
            if (isset($dataClass) && class_exists($dataClass)) {
                $ref         = new \ReflectionClass($dataClass);
                $constructor = $ref->getConstructor();
                $data        = $constructor && $constructor->getNumberOfRequiredParameters()
                    ? $ref->newInstanceWithoutConstructor()
                    : $ref->newInstance();

                $form->setData($data);
            } else {
                $data = [];
            }
            $event = new EmbeddedFormSubmitBeforeEvent($data, $formEntity);
            $eventDispatcher = $this->get('event_dispatcher');
            $eventDispatcher->dispatch(EmbeddedFormSubmitBeforeEvent::EVENT_NAME, $event);
            $this->submitPostPutRequest($form, $request);

            $event = new EmbeddedFormSubmitAfterEvent($data, $formEntity, $form);
            $eventDispatcher->dispatch(EmbeddedFormSubmitAfterEvent::EVENT_NAME, $event);

            //Initialize Reporter
            $diamanteUserRepository = $this->get('diamante.user.repository');
            $diamanteUser = $diamanteUserRepository->findUserByEmail($data['emailAddress']);
            if ($diamanteUser === null) {
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

            if ($form->isSubmitted() && $form->isValid()) {
                $entity = $form->getData();

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
    /**
     * Submits data from post or put Request to a given form.
     *
     * @param FormInterface $form
     * @param Request $request
     * @param bool $clearMissing
     */
    private function submitPostPutRequest(FormInterface $form, Request $request, bool $clearMissing = true)
    {
        $requestData = $form->getName()
            ? $request->request->get($form->getName(), [])
            : $request->request->all();

        $filesData = $form->getName()
            ? $request->files->get($form->getName(), [])
            : $request->files->all();

        $form->submit(array_replace_recursive($requestData, $filesData), $clearMissing);
    }

}