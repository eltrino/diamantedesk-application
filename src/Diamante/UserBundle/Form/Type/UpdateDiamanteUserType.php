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

namespace Diamante\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UpdateDiamanteUserType extends CreateDiamanteUserType
{
    /**
     * @inheritDoc
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Diamante\UserBundle\Api\Command\UpdateDiamanteUserCommand',
                'intention'          => 'diamante_user',
                'cascade_validation' => true,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'diamante_user_update';
    }

}