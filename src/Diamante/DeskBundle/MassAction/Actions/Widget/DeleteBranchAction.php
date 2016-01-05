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

namespace Diamante\DeskBundle\MassAction\Actions\Widget;

use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Widget\WindowMassAction;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

class DeleteBranchAction extends WindowMassAction
{
    public function getName()
    {
        return 'delete_branch';
    }

    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['route'])) {
            $options['route'] = 'diamante_branch_mass_action';
        }

        return parent::setOptions($options);
    }

}