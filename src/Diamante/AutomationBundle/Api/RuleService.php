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

namespace Diamante\AutomationBundle\Api;

use Diamante\AutomationBundle\Model\Rule;
use Diamante\AutomationBundle\Entity\TimeTriggeredRule;
use Diamante\AutomationBundle\Entity\EventTriggeredRule;

/**
 * Interface RuleService
 *
 * @package Diamante\AutomationBundle\Api
 */
interface RuleService
{
    /**
     * @param string $type
     * @param string $id
     *
     * @return TimeTriggeredRule|EventTriggeredRule
     */
    public function viewRule($type, $id);

    /**
     * @param string $input
     *
     * @return TimeTriggeredRule|EventTriggeredRule
     */
    public function createRule($input);

    /**
     * @param string $input
     * @param string $id
     *
     * @return TimeTriggeredRule|EventTriggeredRule
     */
    public function updateRule($input, $id);

    /**
     * @param string $type
     * @param string $id
     */
    public function deleteRule($type, $id);

    /**
     * @param string $type
     * @param string $id
     *
     * @return Rule
     */
    public function activateRule($type, $id);

    /**
     * @param string $type
     * @param string $id
     *
     * @return Rule
     */
    public function deactivateRule($type, $id);
}
