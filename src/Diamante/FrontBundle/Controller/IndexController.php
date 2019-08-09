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
namespace Diamante\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/")
 */
class IndexController extends Controller
{
    /**
     * @Route(
     *      "/",
     *      name="diamante_front_index"
     * )
     * @Template("DiamanteFrontBundle::index.html.twig")
     *
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $settings = $this->container->get('diamante.email_processing.mail_system_settings');

        return [
            'apiUrl' => $request->getUriForPath('/api/diamante/rest/latest'),
            'baseUrl' => $request->getBaseUrl() . $request->getPathInfo(),
            'basePath' => $request->getBasePath(),
            'branchId' => $settings->getDefaultBranchId()
        ];
    }
}
