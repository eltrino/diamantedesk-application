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

use Diamante\DeskBundle\Infrastructure\Shared\Exception\Flashable;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

trait ExceptionHandlerTrait
{
    /**
     * @var Logger
     */
    protected $logger;

    /** @var
     * array
     */
    protected $ignoredExceptions = [
        MethodNotAllowedException::class
    ];

    /**
     * @param \Exception $e
     * @param bool $showFlashMessage
     * @param bool $skipIgnored
     * @return array
     */
    protected function handleException(
        \Exception $e,
        $showFlashMessage = true,
        $skipIgnored = true
    )
    {
        if (in_array(get_class($e), $this->ignoredExceptions) && $skipIgnored) {
            return null;
        }

        if ($e instanceof Flashable) {
            $message = $e->getFlashMessage();
        } else {
            $message = 'Exception occurred';
        }

        $this->get('monolog.logger.diamante')
            ->error(
                sprintf("%s: %s", $message, $e->getMessage())
            );

        if ($showFlashMessage) {
            $this->addErrorMessage($message);
        }
    }
}