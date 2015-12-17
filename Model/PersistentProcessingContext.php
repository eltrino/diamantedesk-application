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

namespace Diamante\AutomationBundle\Model;


class PersistentProcessingContext
{
    const STATE_NEW         = 'new';
    const STATE_IN_PROGRESS = 'in_progress';
    const STATE_PROCESSED   = 'processed';
    const STATE_INCOMPLETE  = 'incomplete';

    protected $id;
    protected $targetEntityId;
    protected $targetEntityClass;
    protected $targetEntityChangeset;
    protected $state = self::STATE_NEW;

    public function __construct($id, $class, array $changeSet = [])
    {
        $this->targetEntityId        = $id;
        $this->targetEntityClass     = $class;
        $this->targetEntityChangeset = $changeSet;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTargetEntityId()
    {
        return $this->targetEntityId;
    }

    /**
     * @return mixed
     */
    public function getTargetEntityClass()
    {
        return $this->targetEntityClass;
    }

    /**
     * @return mixed
     */
    public function getTargetEntityChangeset()
    {
        return $this->targetEntityChangeset;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    public function lock()
    {
        $this->state = self::STATE_IN_PROGRESS;
    }

    public function release()
    {
        $this->state = self::STATE_PROCESSED;
    }

    public function markIncomplete()
    {
        $this->state = self::STATE_INCOMPLETE;
    }
}