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

use Symfony\Component\HttpFoundation\Response;

/**
 * Class AutomationController
 *
 * @package Diamante\AutomationBundle\Controller
 *
 * @Route("automation")
 */
class AutomationController
{
    /**
     * @Route(
     *      "/{type}/{_format}",
     *      name="diamante_automation_list",
     *      requirements={"type"="\w+", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template("DiamanteAutomationBundle:Automation:list.html.twig")
     */
    public function listAction($type)
    {
        return [];
    }

    /**
     * @Route(
     *      "/{type}/view/{id}",
     *      name="diamante_automation_view",
     *      requirements={"type"="\w+", "id"="^[a-zA-Z0-9-]*$"}
     * )
     * @Template("DiamanteAutomationBundle:Automation:view.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function viewAction($type, $id)
    {
        return new Response();
    }

    /**
     * @Route(
     *      "/{type}/create",
     *      name="diamante_automation_create",
     *      requirements={"type"="\w+"}
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
     *      requirements={"type"="\w+", "id"="^[a-zA-Z0-9-]*$"}
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
     *      requirements={"type"="\w+", "id"="^[a-zA-Z0-9-]*$"}
     * )
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($type, $id)
    {
        return new Response();
    }
}
