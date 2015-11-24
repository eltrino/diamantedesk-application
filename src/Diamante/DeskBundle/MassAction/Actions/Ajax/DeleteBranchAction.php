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

namespace Diamante\DeskBundle\MassAction\Actions\Ajax;

use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

class DeleteBranchAction extends DeleteMassAction
{
    public function getName()
    {
        return 'delete_branch';
    }

    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['handler'])) {
            $options['handler'] = 'diamante.desk.mass.action.delete_branch.handler';
        }

        return parent::setOptions($options);
    }

}