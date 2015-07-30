<?php

namespace Diamante\AutomationBundle\Controller;

use Diamante\AutomationBundle\Rule\Engine\EngineImpl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Diamante\AutomationBundle\Api\Command\RuleCommand;

/**
 * Class RuleController
 * @package Diamante\AutomationBundle\Controller
 *
 */
class RuleController extends Controller
{
    const LOAD = 'load';
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const ACTIVATE = 'activate';
    const DEACTIVATE = 'deactivate';

    /**
     * @Route("/debug", name="diamante_automation_debug")
     * @Template()
     */
    public function debugAction()
    {
        $engine = $this->container->get('diamante_automation.engine');

        $tickets = $this->container->get('diamante.ticket.repository')->getAll();

        $fact = $engine->createFact($tickets[0]);

        $result = $engine->check($fact);

        if ($result) {
            $engine->runAgenda();
        }

        $engine->reset();

        return [];
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="diamante_automation_list",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     */
    public function listAction()
    {

    }

    /**
     * @Route(
     *      "/view/{id}",
     *      name="diamante_automation_view",
     *      requirements={"id"="\d+"}
     * )
     * @Template
     *
     * @param int $id
     *
     * @return array
     */
    public function viewAction($id)
    {
        $command = new RuleCommand();
        $command->id = $id;
        $command->mode = EngineImpl::MODE_WORKFLOW;

        try {
            $rule = $this->get('diamante.rule.service')->actionRule($command, self::LOAD);
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule loading failed: %s', $e->getMessage())
            );

            return new Response($e->getMessage(), 404);
        }

        return ['entity'  => $rule];
    }

    /**
     * @Route(
     *      "/create",
     *      name="diamante_automation_create"
     * )
     */
    public function createAction()
    {
        $command = new RuleCommand();
        $command->mode = EngineImpl::MODE_WORKFLOW;
        $command->weight = 0;
        $command->active = 1;
        $command->expression = 'AND';
        $command->condition = 'eq[status, new]';
        $command->action = 'notify[channel:email, recipients:{akolomiec1989@gmail.com}]';

        try {
            $rule = $this->get('diamante.rule.service')->actionRule($command, self::CREATE);

            return $rule->getId();
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule creation failed: %s', $e->getMessage())
            );
            $this->addErrorMessage('diamante.automation.rule.messages.create.error');

            return $this->redirect(
                $this->generateUrl(
                    'diamante_automation_create'
                )
            );
        }
    }

    /**
     * @Route(
     *      "/update/{id}",
     *      name="diamante_automation_update",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     * @return array
     */
    public function updateAction($id)
    {
        $command = new RuleCommand();
        $command->id = $id;
        $command->mode = EngineImpl::MODE_WORKFLOW;

        try {
            $rule = $this->get('diamante.rule.service')->actionRule($command, self::LOAD);
            $command = RuleCommand::fromRule($rule);
            $rule = $this->get('diamante.rule.service')->actionRule($command, self::UPDATE);

            return $rule->getId();
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule creation failed: %s', $e->getMessage())
            );
            $this->addErrorMessage('diamante.automation.rule.messages.update.error');

            return $this->redirect(
                $this->generateUrl(
                    'diamante_automation_create'
                )
            );
        }
    }

    /**
     * @Route(
     *      "/delete/{id}",
     *      name="diamante_automation_delete",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $command = new RuleCommand();
        $command->id = $id;
        $command->mode = EngineImpl::MODE_WORKFLOW;

        try {
            $this->get('diamante.rule.service')->actionRule($command, self::DELETE);

            return new Response(null, 204, array(
                    'Content-Type' => $this->getRequest()->getMimeType('json')
                ));
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(sprintf('Rule deletion failed: %s', $e->getMessage()));
            $this->addErrorMessage('diamante.automation.rule.messages.delete.error');
            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @Route(
     *      "/activate/{id}",
     *      name="diamante_automation_activate",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function activateAction($id)
    {
        $command = new RuleCommand();
        $command->id = $id;
        $command->mode = EngineImpl::MODE_WORKFLOW;

        try {
            $rule = $this->get('diamante.rule.service')->actionRule($command, self::ACTIVATE);
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule activation failed: %s', $e->getMessage())
            );
            $this->addErrorMessage('diamante.automation.rule.messages.activation.error');

            return new Response($e->getMessage(), 500);
        }

        $this->addSuccessMessage('diamante.automation.rule.messages.activation.success');
        $response = $this->redirect(
            $this->generateUrl(
                'diamante_automation_view',
                array('id' => $rule->getId())
            )
        );

        return $response;
    }

    /**
     * @Route(
     *      "/deactivate/{id}",
     *      name="diamante_automation_deactivate",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function deactivateAction($id)
    {
        $command = new RuleCommand();
        $command->id = $id;
        $command->mode = EngineImpl::MODE_WORKFLOW;

        try {
            $rule = $this->get('diamante.rule.service')->actionRule($command, self::DEACTIVATE);
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule deactivation failed: %s', $e->getMessage())
            );
            $this->addErrorMessage('diamante.automation.rule.messages.deactivation.error');

            return new Response($e->getMessage(), 500);
        }

        $this->addSuccessMessage('diamante.automation.rule.messages.deactivation.success');
        $response = $this->redirect($this->generateUrl(
                'diamante_automation_view',
                array('id' => $rule->getId())
            ));

        return $response;
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
     * @param $message
     */
    private function addSuccessMessage($message)
    {
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans($message)
        );
    }
}
