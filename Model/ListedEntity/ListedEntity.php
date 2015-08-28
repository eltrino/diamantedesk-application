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

namespace Diamante\AutomationBundle\Model\ListedEntity;

/**
 * DTO for provide listed entity data
 *
 * Class ListedEntity
 * @package Diamante\AutomationBundle\Model\ListedEntity
 */
class ListedEntity
{
    /**
     * @var string
     */
    protected $listedEntityClassName;

    /**
     * @var string
     */
    protected $entityProcessorClassName;

    /**
     * @var string
     */
    protected $entityProcessor;

    /**
     * @param string $listedEntityClassName
     * @param $entityProcessorClassName
     */
    public function __construct($listedEntityClassName, $entityProcessorClassName)
    {
        $this->listedEntityClassName = $listedEntityClassName;
        $this->entityProcessorClassName = $entityProcessorClassName;
    }

    /**
     * @return string
     */
    public function getListedEntityClassName()
    {
        return $this->listedEntityClassName;
    }

    /**
     * @param string $listedEntityClassName
     */
    public function setListedEntityClassName($listedEntityClassName)
    {
        $this->listedEntityClassName = $listedEntityClassName;
    }

    /**
     * @return string
     */
    public function getEntityProcessorClassName()
    {
        return $this->entityProcessor;
    }

    /**
     * @return ProcessorInterface
     */
    public function getEntityProcessor()
    {
        if (!$this->entityProcessor) {
            $this->entityProcessor = new $this->entityProcessorClassName;
        }

        return $this->entityProcessor;
    }

    /**
     * @param $entityProcessorClassName
     */
    public function setEntityProcessorClassName($entityProcessorClassName)
    {
        $this->entityProcessorClassName = $entityProcessorClassName;
    }
}