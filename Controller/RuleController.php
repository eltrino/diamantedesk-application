<?php

namespace Diamante\AutomationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class RuleController
 * @package Diamante\AutomationBundle\Controller
 *
 */
class RuleController extends Controller
{
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
     */
    public function viewAction($id)
    {

    }

    /**
     * @Route(
     *      "/create/{id}",
     *      name="diamante_automation_create",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     */
    public function createAction($id)
    {

    }

    /**
     * @Route(
     *      "/update/{id}",
     *      name="diamante_automation_update",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     */
    public function updateAction($id)
    {

    }

    /**
     * @Route(
     *      "/delete/{id}",
     *      name="diamante_automation_delete",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     */
    public function deleteAction($id)
    {

    }

    /**
     * @Route(
     *      "/activate/{id}",
     *      name="diamante_automation_activate",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     */
    public function activateAction($id)
    {

    }

    /**
     * @Route(
     *      "/deactivate/{id}",
     *      name="diamante_automation_deactivate",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     */
    public function deactivateAction($id)
    {

    }
}
