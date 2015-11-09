<?php

namespace Diamante\AutomationBundle\Controller;

use Diamante\AutomationBundle\Rule\Engine\EngineImpl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Diamante\AutomationBundle\Api\Command\RuleCommand;
use Diamante\AutomationBundle\Api\Command\ConditionCommand;
use JMS\Serializer\SerializerBuilder;
use Diamante\AutomationBundle\Form\Type\CreateRuleType;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Diamante\AutomationBundle\Model\Shared\AutomationRule;

/**
 * Class RuleController
 *
 * @package Diamante\AutomationBundle\Controller
 */
abstract class RuleController extends Controller
{
    use \Diamante\DeskBundle\Controller\Shared\FormHandlerTrait;
    use \Diamante\DeskBundle\Controller\Shared\ExceptionHandlerTrait;
    use \Diamante\DeskBundle\Controller\Shared\ResponseHandlerTrait;

    const LOAD = 'load';
    const CREATE = 'create';
    const UPDATE = 'update';
    const VIEW = 'view';
    const DELETE = 'delete';
    const ACTIVATE = 'activate';
    const DEACTIVATE = 'deactivate';
    const CONDITION_COMMAND = 'Diamante\AutomationBundle\Api\Command\ConditionCommand';

    public function listAction()
    {
        return [];
    }

    public function viewAction($id)
    {
        try {
            $loadCommand = $this->createLoadRuleCommand($id);
            $rule = $this->get('diamante.rule.service')->actionRule($loadCommand, self::LOAD);
            $viewCommand = $this->createViewRuleCommand($rule);
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule loading failed: %s', $e->getMessage())
            );

            return new Response($e->getMessage(), 404);
        }

        return ['entity' => $rule, 'conditions' => $viewCommand->conditions];
    }

    public function createAction()
    {
        $command = new RuleCommand();
        try {
            $form = $this->createForm('diamante_rule_form', $command);
            $result = $this->edit(
                $command,
                $form,
                function ($command) {
                    return $this->get('diamante.rule.service')->actionRule($command, self::CREATE);
                }
            );
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule creation failed: %s', $e->getMessage())
            );
            $this->addErrorMessage('diamante.automation.rule.messages.create.error');

            return $this->redirect(
                $this->generateUrl(
                    $this->getRoute(self::CREATE)
                )
            );
        }

        return $result;
    }

    public function updateAction($id)
    {
        $loadCommand = $this->createLoadRuleCommand($id);
        $rule = $this->get('diamante.rule.service')->actionRule($loadCommand, self::LOAD);
        $viewCommand = $this->createViewRuleCommand($rule);

        try {
            $form = $this->createForm('diamante_rule_form', $viewCommand);
            $result = $this->edit(
                $viewCommand,
                $form,
                function ($command) {
                    return $this->get('diamante.rule.service')->actionRule($command, self::UPDATE);
                }
            );
        } catch (MethodNotAllowedException $e) {
            return $this->redirect(
                $this->generateUrl(
                    $this->getRoute(self::VIEW),
                    array(
                        'id' => $id
                    )
                )
            );
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule creation failed: %s', $e->getMessage())
            );
            $this->addErrorMessage('diamante.automation.rule.messages.update.error');

            return $this->redirect(
                $this->generateUrl(
                    $this->getRoute(self::UPDATE)
                )
            );
        }

        return $result;

    }

    private function edit(RuleCommand $command, $form, $callback)
    {
        $response = null;
        try {
            $this->handle($form);
            $editCommand = $this->createEditRuleCommand($command);

            $rootRuleId = $callback($editCommand);

            $response = $this->getSuccessSaveResponse(
                $this->getRoute(self::UPDATE),
                $this->getRoute(self::VIEW),
                ['id' => $rootRuleId]
            );
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = array('form' => $form->createView());
        }

        return $response;
    }

    public function deleteAction($id)
    {
        $command = $this->createLoadRuleCommand($id);

        try {
            $this->get('diamante.rule.service')->actionRule($command, self::DELETE);

            return new Response(
                null, 204, array(
                    'Content-Type' => $this->getRequest()->getMimeType('json')
                )
            );
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule deletion failed: %s', $e->getMessage())
            );
            $this->addErrorMessage('diamante.automation.rule.messages.delete.error');

            return new Response($e->getMessage(), 500);
        }
    }

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
                $this->getRoute(self::VIEW),
                array('id' => $rule->getId())
            )
        );

        return $response;
    }

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
        $response = $this->redirect(
            $this->generateUrl(
                $this->getRoute(self::VIEW),
                array('id' => $rule->getId())
            )
        );

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

    private function getRoute($action)
    {
        return sprintf('diamante_%s_%s', static::MODE, $action);
    }

    private function createViewRuleCommand(AutomationRule $rule)
    {
        $serializer = SerializerBuilder::create()->build();
        $command = new RuleCommand();
        $conditionCommand = ConditionCommand::createFromRule($rule);

        $command->id = $rule->getId();
        $command->name = 'name';
        $command->conditions = $serializer->serialize($conditionCommand, 'json');
        $command->actions = 'actions';
        $command->mode = static::MODE;

        return $command;
    }

    private function createEditRuleCommand(RuleCommand $command)
    {
        $serializer = SerializerBuilder::create()->build();

        $command->conditions = $serializer->deserialize(
            $command->conditions,
            self::CONDITION_COMMAND,
            'json'
        );
        $command->mode = static::MODE;

        return $command;
    }

    private function createLoadRuleCommand($id)
    {
        $command = new RuleCommand();
        $command->id = $id;
        $command->mode = static::MODE;

        return $command;
    }
}
