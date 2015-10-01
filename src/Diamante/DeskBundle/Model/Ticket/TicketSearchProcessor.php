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

use Diamante\DeskBundle\Model\Ticket\Filter\TicketFilterCriteriaProcessor;

class TicketSearchProcessor extends TicketFilterCriteriaProcessor
{
    /**
     * @var string
     */
    protected $searchQuery;

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        if (!$this->searchQuery) {
            foreach ($this->dataProperties as $key => $property) {
                if ($property == 'q') {
                    $value = urldecode($this->command->{$property});
                    $this->setSearchQuery($value);
                    unset($this->dataProperties[$key]);
                    break;
                }
            }
            $this->dataProperties = array_values($this->dataProperties);
        }
        return $this->searchQuery;
    }

    /**
     * @param string $query
     * @return $this
     */
    protected function setSearchQuery($query)
    {
        $this->searchQuery = $query;

        return $this;
    }

}