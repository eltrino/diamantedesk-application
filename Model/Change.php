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

/**
 * DTO for providing field changes
 * Class Change
 * @package Diamante\AutomationBundle\Model
 */
class Change
{
    /**
     * @var mixed
     */
    private $oldValue;

    /**
     * @var mixed
     */
    private $newValue;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param $fieldName
     * @param $oldValue
     * @param $newValue
     */
    public function __construct($fieldName, $oldValue, $newValue)
    {
        $this->fieldName = $fieldName;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

}