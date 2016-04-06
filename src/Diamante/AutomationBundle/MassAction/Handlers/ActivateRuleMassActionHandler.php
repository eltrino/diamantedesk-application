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

namespace Diamante\AutomationBundle\MassAction\Handlers;

use Diamante\AutomationBundle\Model\Rule;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;

class ActivateRuleMassActionHandler extends RuleStateMassActionHandler
{
    /**
     * @var string
     */
    const RESPONSE_MESSAGE = 'diamante.automation.rule.actions.mass.response.activate.success_message';

    /**
     * @param MassActionHandlerArgs $args
     *
     * @return MassActionResponse
     * @throws \Exception
     */
    public function handle(MassActionHandlerArgs $args)
    {
        return $this->handleState(
            $args,
            function(Rule $entity) {
                $entity->activate();
            }
        );
    }
}
