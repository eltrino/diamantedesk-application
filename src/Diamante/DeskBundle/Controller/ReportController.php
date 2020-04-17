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
 * @Route("reports")
 */
class ReportController extends Controller
{
    use Shared\ExceptionHandlerTrait;
    use Shared\SessionFlashMessengerTrait;

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

            return [
                'page_title' => $this->getReportLabel($id),
                'chart_type' => $this->getChartType($id),
                'data'       => $data,
            ];
        } catch (\Exception $e) {
            $this->handleException($e);
            throw $this->createNotFoundException($e->getMessage(), $e);
        }
    }

    /**
     * @Route(
     *      "/widget/{id}",
     *      name="diamante_report_widget",
     *      options={"expose"=true}
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
                    'chart_type' => $this->getChartType($id),
                    'data'       => $this->get('diamante.report.service')->build($id),
                ],
                $manager->getWidgetAttributesForTwig($id)
            );

            return $this->render(
                'DiamanteDeskBundle:Dashboard:widget.html.twig',
                $params
            );

        } catch (\Exception $e) {
            $this->handleException($e);
            throw $this->createNotFoundException($e->getMessage(), $e);
        }
    }


    /**
     * @Route(
     *      "/widgetMyRecentTickets/{id}",
     *      name="diamante_myrecenttickets_widget",
     *      options={"expose"=true}
     * )
     *
     *
     * @param string $id
     * @return array
     */
    public function myRecentTicketsWidgetAction($id)
    {
        try {
            $manager = $this->get('oro_dashboard.widget_configs');

            $params = $manager->getWidgetAttributesForTwig($id);

            return $this->render(
                'DiamanteDeskBundle:Dashboard:ticketWidget.html.twig',
                $params
            );

        } catch (\Exception $e) {
            $this->handleException($e);
            throw $this->createNotFoundException($e->getMessage(), $e);
        }
    }

    /**
     * @param $id
     * @return string
     */
    private function getChartType($id)
    {
        $config = $this->get('diamante.report.service')->getConfig($id);
        return $config['chart']['type'];
    }

    /**
     * @param $id
     * @return string
     */
    private function getReportLabel($id)
    {
        $config = $this->get('diamante.report.service')->getConfig($id);
        return $config['label'];
    }
}
