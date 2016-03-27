<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Infrastructure\Changeset;

use Diamante\DeskBundle\Model\Shared\Property;
use Doctrine\ORM\PersistentCollection;

/**
 * Class ChangesetBuilder
 *
 * @package Diamante\AutomationBundle\Infrastructure\Changeset
 */
class Changeset
{
    /**
     * @var array
     */
    private $changeset;

    /**
     * @var string
     */
    private $action;

    private $exclude
        = [
            'id',
            'uniqueId',
            'sequenceNumber',
            'watcherList',
            'createdAt',
            'updatedAt',
            'statusUpdatedSince',
            'assignedSince',
            'tags',
            'key',
            'comments'
        ];

    /**
     * Changeset constructor.
     *
     * @param array  $changeset
     * @param string $action
     */
    public function __construct(array $changeset, $action)
    {
        $this->changeset = $changeset;
        $this->action = $action;
    }

    public function getDiff()
    {
        $actionMethod = sprintf('get%sDiff', ucfirst($this->action));

        return $this->$actionMethod();
    }

    /**
     * @return array
     */
    private function getCreatedDiff()
    {
        $diff = [];

        foreach ($this->changeset as $property => $values) {
            list($old, $new) = $values;
            if (!in_array($property, $this->exclude)) {
                if ($new instanceof PersistentCollection) {
                    $new = $this->getCollectionValues($new);
                } elseif ($new instanceof Property) {
                    $new = $new->getValue();
                }

                $diff[$property] = ['old' => $old, 'new' => $new];
            }
        }

        return $diff;
    }

    /**
     * @return array
     */
    private function getUpdatedDiff()
    {
        $diff = [];

        foreach ($this->changeset as $property => $values) {
            list($old, $new) = $values;
            if (!in_array($property, $this->exclude)) {
                if ($new instanceof PersistentCollection) {
                    $old = $this->getCollectionValues($old);
                    $new = $this->getCollectionValues($new);
                } elseif ($new instanceof Property) {
                    $old = $old->getValue();
                    $new = $new->getValue();
                }

                if ($old == $new) {
                    continue;
                }

                $diff[$property] = ['old' => $old, 'new' => $new];
            }
        }

        return $diff;
    }

    /**
     * @return array
     */
    private function getRemovedDiff()
    {
        return [];
    }

    /**
     * @param PersistentCollection $collection
     *
     * @return array
     */
    private function getCollectionValues(PersistentCollection $collection)
    {
        $values = [];
        foreach ($collection->getValues() as $entity) {
            $values[] = $entity->getFilename();
        }

        return $values;
    }
}