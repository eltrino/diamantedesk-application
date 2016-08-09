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

namespace Diamante\AutomationBundle\Rule\Fact;

class EventTriggeredFact extends AbstractFact
{
    /**
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $editor;

    /**
     * @var array
     */
    protected $targetChangeset;

    /**
     * @param       $entity
     * @param       $targetType
     * @param       $action
     * @param       $editor
     * @param array $targetChangeset
     */
    public function __construct($entity, $targetType, $action, $editor, $targetChangeset)
    {
        $this->action = $action;
        $this->editor = $editor;
        $this->targetChangeset = $targetChangeset;

        parent::__construct($entity, $targetType);
    }

    /**
     * @return array
     */
    public function getTargetChangeset()
    {
        return $this->targetChangeset;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getEditor()
    {
        return $this->editor;
    }
}