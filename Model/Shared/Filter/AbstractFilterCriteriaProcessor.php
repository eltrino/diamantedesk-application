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

namespace Diamante\DeskBundle\Model\Shared\Filter;

use Diamante\DeskBundle\Api\Command\Filter\CommonFilterCommand;

abstract class AbstractFilterCriteriaProcessor implements FilterCriteriaProcessor
{
    const EQ_OPERATOR           = 'eq';
    const LIKE_OPERATOR         = 'like';
    const GT_OPERATOR           = 'gt';
    const LT_OPERATOR           = 'lt';
    const GTE_OPERATOR          = 'gte';
    const LTE_OPERATOR          = 'lte';

    const CREATED_AFTER_PROP    = 'createdAfter';
    const CREATED_BEFORE_PROP   = 'createdBefore';
    const UPDATED_AFTER_PROP    = 'updatedAfter';
    const UPDATED_BEFORE_PROP   = 'updatedBefore';

    const TIMESTAMP_FORMAT      = 'Y-m-d H:i:s';

    /**
     * @var
     */
    protected $command;

    /**
     * @var array
     */
    protected $criteria = array();

    /**
     * @var array
     */
    protected $dataProperties = array();
    /**
     * @var array
     */
    protected $pagingProperties = array();

    /**
     * @var array
     */
    protected $timestampProperties = array();

    /**
     * @var FilterPagingProperties
     */
    protected $pagingConfig;

    /**
     * @param CommonFilterCommand $command
     */
    public function setCommand(CommonFilterCommand $command)
    {
        $this->command = $command;
        $this->populateProperties();
    }

    /**
     * @return array
     */
    public function getCriteria()
    {
        if (empty($this->criteria)) {
            $this->buildCriteria();
            $this->processTimestampProperties();
        }

        return $this->criteria;
    }

    /**
     * @return FilterPagingProperties
     */
    public function getPagingProperties()
    {
        if (empty($this->pagingConfig)) {
            $this->createPagingConfig();
        }

        return $this->pagingConfig;
    }


    protected function createPagingConfig()
    {
        foreach ($this->pagingProperties as $propertyName) {
            $pagingConfig[$propertyName] = $this->command->{$propertyName};
        }

        $this->pagingConfig = FilterPagingProperties::fromArray($pagingConfig);
    }

    protected function populateProperties()
    {
        $reflection = new \ReflectionClass($this->command);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $this->timestampProperties = array(
            self::CREATED_AFTER_PROP,
            self::CREATED_BEFORE_PROP,
            self::UPDATED_AFTER_PROP,
            self::UPDATED_BEFORE_PROP
        );
        $this->pagingProperties    = array(
            FilterPagingProperties::PAGE_PROP_NAME,
            FilterPagingProperties::PER_PAGE_PROP_NAME,
            FilterPagingProperties::ORDER_PROP_NAME,
            FilterPagingProperties::SORT_PROP_NAME
        );

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if (in_array($propertyName, $this->timestampProperties) || in_array($propertyName, $this->pagingProperties)) {
                continue;
            }

            array_push($this->dataProperties, $propertyName);
        }
    }

    protected function processTimestampProperties()
    {
        foreach ($this->timestampProperties as $property) {
            $value = $this->command->{$property};
            if (!empty($value)) {
                switch ($property) {
                    case self::CREATED_AFTER_PROP:
                        array_push($this->criteria, array('createdAt', 'gte', $value));
                        break;
                    case self::UPDATED_AFTER_PROP:
                        array_push($this->criteria, array('updatedAt', 'gte', $value));
                        break;
                    case self::CREATED_BEFORE_PROP:
                        array_push($this->criteria, array('createdAt', 'lt', $value));
                        break;
                    case self::UPDATED_BEFORE_PROP:
                        array_push($this->criteria, array('updatedAt', 'lt', $value));
                        break;
                    default:
                        break;
                }
            }
        }
    }

    abstract protected function buildCriteria();
}