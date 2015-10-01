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

namespace Diamante\DeskBundle\Form\Type;

use Diamante\DeskBundle\Entity\Ticket;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Diamante\DeskBundle\Form\DataTransformer\UserTransformer;

class ReporterSelectType extends DiamanteUserSelectType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => Ticket::UNASSIGNED_LABEL,
                    'result_template_twig'    => 'DiamanteDeskBundle:Search:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'DiamanteDeskBundle:Search:Autocomplete/selection.html.twig'
                ),
                'transformer' => new UserTransformer(),
                'grid_name' => 'diamante-reporter-select-grid',
                'autocomplete_alias' => 'diamante_user'
            )
        );
    }

    public function getName()
    {
        return 'diamante_reporter_select';
    }
} 