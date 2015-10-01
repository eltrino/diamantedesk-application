<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\UserBundle\Twig\Extensions;


use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class PortalUrlExtension extends \Twig_Extension
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var ConfigManager
     */
    protected $config;

    public function __construct(Router $router, ConfigManager $config)
    {
        $this->router = $router;
        $this->config = $config;
    }
    public function getName()
    {
        return 'diamante.portal_url';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'get_password_reset_link',
                [$this, 'getPasswordResetLink'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'get_portal_link',
                [$this, 'getPortalLink'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param $hash
     * @return string
     */
    public function getPasswordResetLink($hash)
    {
        $this->updateRequestContext();
        return sprintf('%s/#newpassword/%s', $this->getPortalLink(), $hash);
    }

    /**
     * @return string
     */
    public function getPortalLink()
    {
        $this->updateRequestContext();
        return $this->router->generate('diamante_front', [], Router::ABSOLUTE_URL);
    }

    private function updateRequestContext()
    {
        $url = $this->config->get('oro_ui.application_url');

        if (empty($url)) {
            throw new \RuntimeException('No Application URL configured, unable to generate links');
        }

        list($scheme, $host, $baseUrl) = $this->getUrlParts($url);

        $context = $this->router->getContext();
        $context->setScheme($scheme);
        $context->setHost($host);

        if (!empty($baseUrl)) {
            $context->setBaseUrl($baseUrl);
        }
    }

    /**
     * @param $url
     * @return array
     */
    private function getUrlParts($url)
    {
        $result = preg_match('/^(http[s]?)\:\/\/([A-Za-z0-9\-\.]+)(:[0-9]+)?(.*)/',$url, $matches);

        if (false === (bool)$result) {
            throw new \RuntimeException('Invalid Application URL configured. Unable to generate links');
        }

        list($subject, $scheme, $host, $port, $baseUrl) = $matches;

        if (!empty($baseUrl) && (0 === strpos($baseUrl, '/'))) {
            $baseUrl = ltrim($baseUrl, '/');
        }

        if (!empty($port)) {
            $host .= $port;
        }

        return [$scheme, $host, $baseUrl];
    }
}