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
use Symfony\Component\HttpFoundation\Request;

abstract class WidgetController extends Controller
{
    use Shared\SessionFlashMessengerTrait;
    use Shared\ExceptionHandlerTrait;
    use Shared\FormHandlerTrait;
    use Shared\RequestGetterTrait;

    /**
     * @param string|null $redirectUrl
     * @param array $redirectParams
     * @param bool|true $reload
     * @return array
     */
    protected function getWidgetResponse($redirectUrl = null, $redirectParams = [], $reload = true)
    {
        $response = ['reload_page' => $reload];

        if (!is_null($redirectUrl) && !empty($redirectParams)) {
            $response['redirect'] = $this->generateUrl($redirectUrl, $redirectParams);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function widgetRedirectRequested()
    {
        return !(bool)$this->container->get('request')->get('no_redirect');
    }
}
