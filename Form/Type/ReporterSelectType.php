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
 
/**
 * Created by PhpStorm.
 * User: s3nt1nel
 * Date: 20/11/14
 * Time: 12:35 PM
 */

namespace Diamante\DeskBundle\Form\Type;

use Diamante\DeskBundle\Entity\Ticket;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReporterSelectType extends DiamanteUserSelectType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => Ticket::UNASSIGNED_LABEL,
                    'result_template_twig' => 'OroUserBundle:User:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'OroUserBundle:User:Autocomplete/selection.html.twig'
                ),
                'autocomplete_alias' => 'users'
            )
        );
    }

    public function getName()
    {
        return 'diamante_reporter_select';
    }
} 