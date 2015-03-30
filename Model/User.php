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
namespace Diamante\UserBundle\Model;

class User
{
    const DELIMITER     = '_';
    const TYPE_ORO      = 'oro';
    const TYPE_DIAMANTE = 'diamante';

    protected $id;
    protected $type;

    public function __construct($id, $type)
    {
        $this->id   = $id;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public static function fromString($stringId)
    {
        list($type, $id) = explode(self::DELIMITER, $stringId);

        return new self($id, $type);
    }

    public function __toString()
    {
        return $this->type . self::DELIMITER . $this->id;
    }

    /**
     * @return bool
     */
    public function isDiamanteUser()
    {
        return ($this->type == self::TYPE_DIAMANTE);
    }

    /**
     * @return bool
     */
    public function isOroUser()
    {
        return ($this->type == self::TYPE_ORO);
    }
} 