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
use Symfony\Component\Routing\RequestContext;

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
        $origContext = $this->updateRequestContext();
        $url = sprintf('%s/#newpassword/%s', $this->getPortalLink(), $hash);
        $this->router->setContext($origContext);
        return $url;
    }

    /**
     * @return string
     */
    public function getPortalLink()
    {
        $origContext = $this->updateRequestContext();
        $url = $this->router->generate('diamante_front', [], Router::ABSOLUTE_URL);
        $this->router->setContext($origContext);
        return $url;
    }

    /**
     * @return RequestContext
     */
    private function updateRequestContext()
    {
        $url = $this->config->get('oro_ui.application_url');

        if (empty($url)) {
            throw new \RuntimeException('No Application URL configured, unable to generate links');
        }

        $context = $this->router->getContext();
        $origContext = clone $context;
        $this->setUrlInContext($url, $context);

        return $origContext;
    }

    /**
     * @param $url
     * @param RequestContext $context
     * @return array
     */
    private function setUrlInContext($url, RequestContext $context)
    {
        $parts = parse_url($url);
        if (false === (bool)$parts) {
            throw new \RuntimeException('Invalid Application URL configured. Unable to generate links');
        }

        if (isset($parts['schema'])) {
            $context->setScheme($parts['schema']);
        }

        if (isset($parts['host'])) {
            $context->setHost($parts['host']);
        }

        if (isset($parts['port'])) {
            $context->setHttpPort($parts['port']);
            $context->setHttpsPort($parts['port']);
        }

        if (isset($parts['path'])) {
            $context->setBaseUrl(rtrim($parts['path'], '/'));
        }

        if (isset($parts['query'])) {
            $context->setQueryString($parts['query']);
        }
    }
}
