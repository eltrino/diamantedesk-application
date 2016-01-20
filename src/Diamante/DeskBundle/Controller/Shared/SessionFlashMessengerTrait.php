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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

trait SessionFlashMessengerTrait
{
    /**
     * @param string $message
     * @param array  $parameters
     * @param int    $number
     */
    protected function addSuccessMessage($message, $parameters = [], $number = 0)
    {
        /** @var Controller $this */
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->transChoice($message, $number, $parameters)
        );
    }

    /**
     * @param string $message
     * @param array  $parameters
     * @param int    $number
     */
    protected function addErrorMessage($message, $parameters = [], $number = 0)
    {
        /** @var Controller $this */
        $this->get('session')->getFlashBag()->add(
            'error',
            $this->get('translator')->transChoice($message, $number, $parameters)
        );
    }
}