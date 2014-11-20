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
 
/**
 * Created by PhpStorm.
 * User: s3nt1nel
 * Date: 20/11/14
 * Time: 2:11 PM
 */

namespace Diamante\DeskBundle\Search;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;

class DiamanteUserSearchHandler implements SearchHandlerInterface
{
    protected $properties;
    protected $entityName;

    public function __construct($entityName, array $properties)
    {
        $this->properties = $properties;
        $this->entityName = $entityName;
    }

    /**
     * Converts item into an array that represents it in view.
     *
     * @param mixed $item
     * @return array
     */
    public function convertItem($item)
    {
        $convertedItem = array();

        foreach ($this->properties as $property){
            $convertedItem[$property] = $this->getPropertyValue($property, $item);
        }

        return $convertedItem;
    }

    /**
     * Gets search results, that includes found items and any additional information.
     *
     * @param string $query
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search($query, $page, $perPage)
    {
        // TODO: Implement search() method.
    }

    /**
     * Gets properties that should be displayed
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Gets entity name that is handled by search
     *
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $name
     * @param object|array $item
     * @return mixed
     */
    protected function getPropertyValue($name, $item)
    {
        $result = null;

        if (is_object($item)) {
            $method = 'get' . str_replace(' ', '', str_replace('_', ' ', ucwords($name)));
            if (method_exists($item, $method)) {
                $result = $item->$method();
            } elseif (isset($item->$name)) {
                $result = $item->$name;
            }
        } elseif (is_array($item) && array_key_exists($name, $item)) {
            $result = $item[$name];
        }

        return $result;
    }
} 