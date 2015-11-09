<?php

namespace Diamante\AutomationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class WorkflowRuleController
 *
 * @package Diamante\AutomationBundle\Controller
 *
 * @Route("workflow")
 */
class WorkflowRuleController extends RuleController
{
    const MODE = 'workflow';

    /**
     * @Route(
     *      "/{_format}",
     *      name="diamante_workflow_list",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template("DiamanteAutomationBundle:Workflow:list.html.twig")
     */
    public function listAction()
    {
        return parent::listAction();
    }

    /**
     * @Route(
     *      "/view/{id}",
     *      name="diamante_workflow_view",
     *      requirements={"id"="\d+"}
     * )
     * @Template("DiamanteAutomationBundle:Workflow:view.html.twig")
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
     *      name="diamante_workflow_create"
     * )
     * @Template("DiamanteAutomationBundle:Workflow:create.html.twig")
     */
    public function createAction()
    {
        return parent::createAction();
    }

    /**
     * @Route(
     *      "/update/{id}",
     *      name="diamante_workflow_update",
     *      requirements={"id"="\d+"}
     * )
     * @Template("DiamanteAutomationBundle:Workflow:update.html.twig")
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
     *      name="diamante_workflow_delete",
     *      requirements={"id"="\d+"}
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
