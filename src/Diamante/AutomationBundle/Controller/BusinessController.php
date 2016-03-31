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

use Diamante\DeskBundle\Controller\Shared;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BusinessController
 *
 * @package Diamante\AutomationBundle\Controller
 *
 * @Route("automation")
 */
class BusinessController extends Controller
{
    use Shared\FormHandlerTrait;
    use Shared\ExceptionHandlerTrait;
    use Shared\SessionFlashMessengerTrait;
    use Shared\ResponseHandlerTrait;
    use AutomationTrait;

    /**
     * @Route(
     *      "/{type}/{_format}",
     *      name="diamante_business_list",
     *      requirements={"type"="business", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template("DiamanteAutomationBundle:Automation:list.html.twig")
     *
     * @param string $type
     *
     * @return array
     */
    public function listAction($type)
    {
        return $this->getList($type);
    }

    /**
     * @Route(
     *      "/{type}/view/{id}",
     *      name="diamante_business_view",
     *      requirements={
     *          "type"="business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     * @Template("DiamanteAutomationBundle:Automation:view.html.twig")
     *
     * @param string $type
     * @param int $id
     *
     * @return array|Response
     */
    public function viewAction($type, $id)
    {
        return $this->view($type, $id);
    }

    /**
     * @Route(
     *      "/{type}/create",
     *      name="diamante_business_create",
     *      requirements={"type"="business"}
     * )
     * @Template("DiamanteAutomationBundle:Automation:create.html.twig")
     *
     * @param string $type
     *
     * @return array
     */
    public function createAction($type)
    {
        return $this->create($type);
    }

    /**
     * @Route(
     *      "/{type}/update/{id}",
     *      name="diamante_business_update",
     *      requirements={
     *          "type"="business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     * @Template("DiamanteAutomationBundle:Automation:update.html.twig")
     *
     * @param string $type
     * @param int    $id
     *
     * @return array
     */
    public function updateAction($type, $id)
    {
        return $this->update($type, $id);
    }

    /**
     * @Route(
     *      "/{type}/delete/{id}",
     *      name="diamante_business_delete",
     *      requirements={
     *          "type"="business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     *
     * @param string $type
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($type, $id)
    {
        return $this->delete($type, $id);
    }

    /**
     * @Route(
     *      "/{type}/activate/{id}",
     *      name="diamante_business_activate",
     *      requirements={
     *          "type"="business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     *
     * @param string $type
     * @param int $id
     *
     * @return Response
     */
    public function activateAction($type, $id)
    {
        return $this->activate($type, $id);
    }

    /**
     * @Route(
     *      "/{type}/deactivate/{id}",
     *      name="diamante_business_deactivate",
     *      requirements={
     *          "type"="business",
     *          "id"="^(?i)[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$"
     *      }
     * )
     *
     * @param string $type
     * @param int $id
     *
     * @return Response
     */
    public function deactivateAction($type, $id)
    {
        return $this->deactivate($type, $id);
    }
}
