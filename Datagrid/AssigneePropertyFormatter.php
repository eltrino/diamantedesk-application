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
namespace Eltrino\DiamanteDeskBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

class AssigneePropertyFormatter extends FieldProperty
{
    /**
     * @param ResultRecordInterface $record
     * @return mixed
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        $value = parent::getRawValue($record);

        if (is_null($value)) {
            $value = $this->translator->trans(\Eltrino\DiamanteDeskBundle\Ticket\Model\Ticket::UNASSIGNED_LABEL);
        }

        return $value;
    }
}
