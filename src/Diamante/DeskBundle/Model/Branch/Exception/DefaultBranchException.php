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
namespace Diamante\DeskBundle\Model\Branch\Exception;

use Diamante\DeskBundle\Infrastructure\Shared\Exception\Flashable;

class DefaultBranchException extends \RuntimeException implements Flashable
{
    protected $flashMessage;

    protected $parameters;

    protected $number;

    public function __construct($message = "", array $parameters = [], $number = 0)
    {
        $this->flashMessage = $message;
        $this->parameters = $parameters;
        $this->number = $number;
    }

    public function getFlashMessage()
    {
        return $this->flashMessage;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getNumber()
    {
        return $this->number;
    }
}