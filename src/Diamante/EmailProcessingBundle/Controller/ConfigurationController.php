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
namespace Diamante\EmailProcessingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("system")
 */
class ConfigurationController extends Controller
{
    /**
     * @Route("/config/channels/help", name="diamante_config_channels_help")
     * @Template
     */
    public function systemAction()
    {
        $versionService = $this->get('diamante.version.service');
        $versions = sprintf('%s|%s', $versionService->getDiamanteVersion(), $versionService->getOroVersion());

        return ['versions' => base64_encode($versions)];
    }
}
