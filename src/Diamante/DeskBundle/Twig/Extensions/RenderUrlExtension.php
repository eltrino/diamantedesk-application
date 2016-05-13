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

use Diamante\UserBundle\Entity\DiamanteUser;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Class RenderUrlExtension
 *
 * @package Diamante\DeskBundle\Twig\Extensions
 */
class RenderUrlExtension extends \Twig_Extension
{
    const ADMIN_URI = 'desk/tickets/view';
    const PORTAL_URI = 'portal/#tickets';

    /**
     * @var ConfigManager
     */
    protected $config;

    /**
     * RenderUrlExtension constructor.
     *
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->config = $configManager;
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
            new \Twig_SimpleFunction(
                'render_ticket_url',
                [$this, 'renderUrl'],
                array('is_safe' => array('html'))
            )
        ];
    }

    /**
     * @param string $key
     * @param object $user
     *
     * @return string
     */
    public function renderUrl($key, $user)
    {
        $applicationUrl = rtrim($this->config->get('oro_ui.application_url'), '/');
        $uri = static::ADMIN_URI;

        if ($user instanceof DiamanteUser) {
            $uri = static::PORTAL_URI;
        }

        return sprintf('%s/%s/%s', $applicationUrl, $uri, $key);
    }
}
