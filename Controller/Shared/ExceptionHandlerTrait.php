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

namespace Diamante\DeskBundle\Controller\Shared;

use Symfony\Bridge\Monolog\Logger;

trait ExceptionHandlerTrait
{
    /**
     * @var Logger
     */
    protected $logger;

    protected function handleException(
        \Exception $e,
        $message = null,
        $flashMessage = null,
        $reloadPage = false,
        $redirectRoute = null,
        $redirectParams = []
    )
    {
        $response = [];

        /** @var \Symfony\Bundle\FrameworkBundle\Controller\Controller $this */
        if (empty($this->logger)) {
            $this->logger = $this->get('monolog.logger.diamante');
        }

        if (!is_null($message)) {
            $this->logger->error(sprintf($message, $e->getMessage()));
        }

        if (!is_null($flashMessage)) {
            $this->addErrorMessage($flashMessage);
        }

        if (!is_null($redirectRoute)) {
            $response['redirect'] = $this->get('router')->generate($redirectRoute, $redirectParams);
        }

        if ($reloadPage) {
            $response['reload_page'] = true;
        }

        return $response;
    }

}