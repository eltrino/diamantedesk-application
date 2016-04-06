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
namespace Diamante\DeskBundle\Model\Ticket;

class UniqueId
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = (string)$id;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->id;
    }

    /**
     * Generate new Ticket Unique Id
     * @return UniqueId
     */
    public static function generate()
    {
        return new UniqueId(md5(uniqid()));
    }
}
