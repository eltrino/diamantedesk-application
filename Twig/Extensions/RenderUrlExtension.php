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

namespace Diamante\DeskBundle\Twig\Extensions;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

class RenderUrlExtension extends \Twig_Extension
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'diamante_url_render_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            'render_ticket_url' => new \Twig_Function_Method(
                $this,
                'renderUrl',
                array('is_safe' => array('html'))
            )
        ];
    }

    /**
     * @param string $key
     * @param bool   $isOroUser
     *
     * @return string
     */
    public function renderUrl($key, $isOroUser)
    {
        $route = 'diamante_ticket_view';
        $url = $this->router->generate($route, ['key' => $key], Router::ABSOLUTE_URL);
        if (!$isOroUser) {
            $url = str_replace('desk/tickets/view', 'diamantefront/#tickets', $url);
        }

        return $url;
    }
}
