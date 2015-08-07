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

class Source
{
    const PHONE     = 'phone';
    const WEB       = 'web';
    const EMAIL     = 'email';

    const LABEL_PHONE       = 'Phone';
    const LABEL_WEB         = 'Web';
    const LABEL_EMAIL       = 'Email';

    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    protected static $valueToLabelMap = array();

    /**
     * @param $source
     */
    public function __construct($source)
    {
        static::initValueLabelsMap();

        if (false === isset(static::$valueToLabelMap[$source])) {
            throw new \InvalidArgumentException('Given source is wrong');
        }

        $this->source = (string) $source;
    }

    /**
     * Initialize static array of value to label priorities map
     */
    private static function initValueLabelsMap()
    {
        if (empty(static::$valueToLabelMap)) {
            static::$valueToLabelMap = [
                self::PHONE     => self::LABEL_PHONE,
                self::WEB       => self::LABEL_WEB,
                self::EMAIL     => self::LABEL_EMAIL
            ];
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->source;
    }

    /**
     * Retrieve label of priority
     * @return string
     */
    public function getLabel()
    {
        return static::$valueToLabelMap[$this->source];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }
}
