<?php

namespace Diamante\AutomationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class DefaultController
 * @package Diamante\AutomationBundle\Controller
 *
 */
class DefaultController extends Controller
{
    /**
     * @Route("/debug", name="diamante_automation_index")
     * @Template()
     */
    public function indexAction()
    {
        $engine = $this->container->get('diamante_automation.engine');

        $tickets = $this->container->get('diamante.ticket.repository')->getAll();

        $fact = $engine->createFact($tickets[0],[]);

        $result = $engine->check($fact);

        if ($result) {
            $engine->runAgenda();
        }

        $engine->reset();

        return [];
    }
}
