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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Diamante\DeskBundle\Controller\Shared;

/**
 * Class AutomationController
 *
 * @package Diamante\AutomationBundle\Controller
 *
 * @Route("automation")
 */
class AutomationController extends Controller
{
    use Shared\ExceptionHandlerTrait;
    use Shared\SessionFlashMessengerTrait;
    use Shared\ResponseHandlerTrait;

    /**
     * @Route(
     *      "/{type}/{_format}",
     *      name="diamante_automation_list",
     *      requirements={"type"="workflow|business", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template("DiamanteAutomationBundle:Automation:list.html.twig")
     *
     * @param $type
     *
     * @return array
     */
    public function listAction($type)
    {
        return ['type' => $type];
    }

    /**
     * @Route(
     *      "/{type}/view/{id}",
     *      name="diamante_automation_view",
     *      requirements={
     *          "type"="workflow|business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     * @Template("DiamanteAutomationBundle:Automation:view.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function viewAction($type, $id)
    {
        try {
            $rule = $this->get('diamante.rule.service')->viewRule($type, $id);
            return ["rule" => $rule, 'type' => $type];
        } catch (\Exception $e) {
            $this->handleException($e);
            return new Response(null, 404);
        }
    }

    /**
     * @Route(
     *      "/{type}/create",
     *      name="diamante_automation_create",
     *      requirements={"type"="workflow|business"}
     * )
     * @Template("DiamanteAutomationBundle:Automation:create.html.twig")
     */
    public function createAction($type)
    {
        return new Response();
    }

    /**
     * @Route(
     *      "/{type}/update/{id}",
     *      name="diamante_automation_update",
     *      requirements={
     *          "type"="workflow|business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     * @Template("DiamanteAutomationBundle:Automation:update.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function updateAction($type, $id)
    {
        return new Response();
    }

    /**
     * @Route(
     *      "/{type}/delete/{id}",
     *      name="diamante_automation_delete",
     *      requirements={
     *          "type"="workflow|business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($type, $id)
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
     * @Route(
     *      "/{type}/activate/{id}",
     *      name="diamante_automation_update",
     *      requirements={
     *          "type"="workflow|business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     *
     * @param $type
     * @param int $id
     *
     * @return array
     */
    public function activateAction($type, $id)
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
     * @Route(
     *      "/{type}/deactivate/{id}",
     *      name="diamante_automation_update",
     *      requirements={
     *          "type"="workflow|business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     *
     * @param $type
     * @param int $id
     *
     * @return array
     */
    public function deactivateAction($type, $id)
    {
        try {
            $this->get('diamante.rule.service')->deactivateRule($type, $id);
        } catch (\Exception $e) {
            $this->handleException($e);
            return new Response(null, 500);
        }

        return new Response(null, 204);
    }
}
