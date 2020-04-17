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

namespace Diamante\AutomationBundle\Controller;

use Diamante\AutomationBundle\Api\Command\UpdateRuleCommand;
use Diamante\AutomationBundle\Form\Type\UpdateRuleType;
use Diamante\DeskBundle\Controller\Shared;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait AutomationTrait
{
    /**
     * @param string $type
     *
     * @return array
     */
    protected function getList($type)
    {
        return ['type' => $type];
    }

    /**
     * @param string $type
     * @param integer $id
     *
     * @return array|Response
     */
    protected function view($type, $id)
    {
        $configProvider = $this->container->get('diamante_automation.config.provider');
        $config = $configProvider->prepareConfigDump($this->container->get('translator.default'));
        try {
            $rule = $this->get('diamante.rule.service')->viewRule($type, $id);
            $serializer = $this->get('serializer');

            return [
                "entity" => $rule,
                "model"  => $serializer->serialize($rule, 'json'),
                "config" => $config,
                "type"   => $type
            ];
        } catch (\Exception $e) {
            $this->handleException($e);

            return new Response(null, 404);
        }
    }

    /**
     * @param string $type
     *
     * @return array
     */
    protected function create(Request $request, $type)
    {
        $command = new UpdateRuleCommand();
        /** @var FormInterface $form */
        $form = $this->createForm(UpdateRuleType::class, $command);
        $formView = $form->createView();

        $configProvider = $this->container->get('diamante_automation.config.provider');
        $config = $configProvider->prepareConfigDump($this->container->get('translator.default'));
        try {
            $this->handle($request, $form);

            $rule = $this->get('diamante.rule.service')->createRule($command->rule);
            $this->addSuccessMessage('diamante.automation.rule.messages.create.success');
            $response = $this->getSuccessSaveResponse(
                $this->getRuleRoute($type, 'update'),
                $this->getRuleRoute($type, 'view'),
                ['type' => $type, 'id' => $rule->getId()]
            );

        } catch (\Exception $e) {
            $this->handleException($e);
            $response = ['form' => $formView, 'type' => $type, 'config' => $config];
        }

        return $response;
    }

    /**
     * @param string $type
     * @param integer $id
     *
     * @return array
     */
    protected function update(Request $request, $type, $id)
    {
        $command = new UpdateRuleCommand();
        /** @var FormInterface $form */
        $form = $this->createForm(UpdateRuleType::class, $command);
        $formView = $form->createView();

        $rule = $this->get('diamante.rule.service')->viewRule($type, $id);
        $serializer = $this->get('serializer');
        $configProvider = $this->container->get('diamante_automation.config.provider');
        $config = $configProvider->prepareConfigDump($this->container->get('translator.default'));
        try {
            $this->handle($request, $form);

            $rule = $this->get('diamante.rule.service')->updateRule($command->rule, $id);

            $this->addSuccessMessage('diamante.automation.rule.messages.update.success');
            $response = $this->getSuccessSaveResponse(
                $this->getRuleRoute($type, 'update'),
                $this->getRuleRoute($type, 'view'),
                ['type' => $type, 'id' => $rule->getId()]
            );

        } catch (\Exception $e) {
            $this->handleException($e);
            $response = [
                'form'   => $formView,
                'type'   => $type,
                'config' => $config,
                'model'  => $serializer->serialize($rule, 'json'),
                'rule' => $rule
            ];
        }

        return $response;
    }

    /**
     * @param string $type
     * @param integer $id
     *
     * @return Response
     */
    protected function delete($type, $id)
    {
        try {
            $this->get('diamante.rule.service')->deleteRule($type, $id);
        } catch (\Exception $e) {
            $this->handleException($e);

            return new Response(null, 500);
        }

        return new Response(null, 204);
    }

    /**
     * @param string $type
     * @param integer $id
     *
     * @return Response
     */
    protected function activate($type, $id)
    {
        try {
            $this->get('diamante.rule.service')->activateRule($type, $id);
        } catch (\Exception $e) {
            $this->handleException($e);

            return new Response(null, 500);
        }

        return new Response(null, 204);
    }

    /**
     * @param string $type
     * @param integer $id
     *
     * @return Response
     */
    protected function deactivate($type, $id)
    {
        try {
            $this->get('diamante.rule.service')->deactivateRule($type, $id);
        } catch (\Exception $e) {
            $this->handleException($e);

            return new Response(null, 500);
        }

        return new Response(null, 204);
    }

    /**
     * @param string $type
     * @param string $action
     *
     * @return string
     */
    private function getRuleRoute($type, $action)
    {
        return sprintf('diamante_%s_%s', $type, $action);
    }
}
