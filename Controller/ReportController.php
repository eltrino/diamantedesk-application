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
namespace Diamante\DeskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("report")
 */
class ReportController extends Controller
{
    /**
     * @Route(
     *      "/{id}",
     *      name="diamante_report_view"
     * )
     *
     * @Template("DiamanteDeskBundle:Report:view.html.twig")
     *
     * @param string $id
     * @return array
     */
    public function viewAction($id)
    {
        try {
            $data = $this->get('diamante.report.service')->build($id);
            return ['data' => $data];
        } catch (\Exception $e) {
            return $this->throwException($e);
        }
    }

    /**
     * @Route(
     *      "/widget/{id}",
     *      name="diamante_report_widget"
     * )
     *
     * @param string $id
     * @return array
     */
    public function getWidgetAction($id)
    {
        try {
            $manager = $this->get('oro_dashboard.widget_configs');

            $params = array_merge(
                [
                    'data' => $this->get('diamante.report.service')->build($id)
                ],
                $manager->getWidgetAttributesForTwig($id)
            );

            return $this->render(
                'DiamanteDeskBundle:Dashboard:widget.html.twig',
                $params
            );

        } catch (\Exception $e) {
            return $this->throwException($e);
        }
    }

    /**
     * @param $e
     * @return Response
     */
    private function throwException(\Exception $e)
    {
        $this->container->get('monolog.logger.diamante')->error(sprintf('Report build failed: %s',
            $e->getMessage()));
        $this->get('session')->getFlashBag()->add(
            'error',
            $this->get('translator')->trans($e->getMessage())
        );
        return new Response($e->getMessage(), 404);
    }
}
