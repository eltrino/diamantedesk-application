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
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class BusinessRuleController
 *
 * @package Diamante\AutomationBundle\Controller
 *
 * @Route("business")
 */
class AutomationController extends RuleController
{
    const MODE = 'business';

    const FORM = 'diamante_business_rule_form';

    /**
     * @Route(
     *      "/{_format}",
     *      name="diamante_business_list",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template("DiamanteAutomationBundle:Business:list.html.twig")
     */
    public function listAction()
    {
        return parent::listAction();
    }

    /**
     * @Route(
     *      "/view/{id}",
     *      name="diamante_business_view",
     *      requirements={"id"="^[a-zA-Z0-9-]*$"}
     * )
     * @Template("DiamanteAutomationBundle:Business:view.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function viewAction($id)
    {
        return parent::viewAction($id);
    }

    /**
     * @Route(
     *      "/create",
     *      name="diamante_business_create"
     * )
     * @Template("DiamanteAutomationBundle:Business:create.html.twig")
     */
    public function createAction()
    {
        return parent::createAction();
    }

    /**
     * @Route(
     *      "/update/{id}",
     *      name="diamante_business_update",
     *      requirements={"id"="^[a-zA-Z0-9-]*$"}
     * )
     * @Template("DiamanteAutomationBundle:Business:update.html.twig")
     *
     * @param int $id
     *
     * @return array
     */
    public function updateAction($id)
    {
        return parent::updateAction($id);
    }

    /**
     * @Route(
     *      "/delete/{id}",
     *      name="diamante_business_delete",
     *      requirements={"id"="^[a-zA-Z0-9-]*$"}
     * )
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        return parent::deleteAction($id);
    }
}
