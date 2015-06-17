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

namespace Diamante\AutomationBundle\Model;

class Target
{
    const TARGET_TYPE_TICKET            = 'ticket';
    const TARGET_TYPE_COMMENT           = 'comment';
    const TARGET_TYPE_TICKET_LABEL      = 'Ticket';
    const TARGET_TYPE_COMMENT_LABEL     = 'Comment';

    /**
     * @var string
     */
    protected $value;

    /**
     * @var array
     */
    protected static $valueToLabelMap = [];

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->init();

        if (false === isset(static::$valueToLabelMap[$value])) {
            throw new \InvalidArgumentException('Given target is wrong');
        }

        $this->value = (string)$value;
    }


    protected function init()
    {
        if (empty(static::$valueToLabelMap)) {
            static::$valueToLabelMap = [
                self::TARGET_TYPE_TICKET  => self::TARGET_TYPE_TICKET_LABEL,
                self::TARGET_TYPE_COMMENT => self::TARGET_TYPE_COMMENT_LABEL,
            ];
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)static::$valueToLabelMap[$this->value];
    }


}