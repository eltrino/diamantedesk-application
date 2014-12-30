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
use Symfony\Component\HttpFoundation\Response;


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
     * @return array
     */
    public function indexAction()
    {
        return [
            'apiUrl' => $this->getRequest()->getUriForPath('/api/rest/latest'),
            'baseUrl' => $this->getRequest()->getBaseUrl(),
            'basePath' => $this->getRequest()->getBasePath()
        ];
    }
}
