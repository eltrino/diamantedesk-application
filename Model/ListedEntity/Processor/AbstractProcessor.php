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

namespace Diamante\AutomationBundle\Model\ListedEntity\Processor;

use Diamante\AutomationBundle\Model\ListedEntity\ProcessorInterface;
use Diamante\DeskBundle\Model\Shared\Entity;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\DataAuditBundle\Entity\AuditField;
use Diamante\AutomationBundle\Model\Change;

class AbstractProcessor
{
    const EMAIL_TEMPLATE_DELIMITER = '[Please reply above this line]';

    /**
     * @param Audit $entityLog
     * @return array
     */
    protected function extractChanges(Audit $entityLog)
    {
        $changes = [];
        /** @var AuditField $field */
        foreach ($entityLog->getFields()->toArray() as $field) {
            $changes[] = new Change(
                $field->getField(),
                $field->getOldValue(),
                $field->getNewValue()
            );
        }
        return $changes;
    }

    /**
     * @param Entity $entity
     * @param ProcessorInterface $processor
     * @param Change $field
     * @return string
     */
    public function getEntityHeader(Entity $entity, ProcessorInterface $processor, Change $field)
    {
        if (!$entity->getId()) {
            return $processor->getEntityDeleteText();
        }

        if ($field->getOldValue() == '') {
            return $processor->getEntityCreateText();
        }

        return $processor->getEntityUpdateText();
    }
}