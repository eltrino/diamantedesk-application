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
namespace Diamante\AutomationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateRuleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'condition',
            'text',
            array(
                'label'    => 'diamante.automation.attributes.condition',
                'required' => true,
            )
        );

        $builder->add(
            'action',
            'text',
            array(
                'label'    => 'diamante.automation.attributes.action',
                'required' => true,
            )
        );

        $builder->add(
            'weight',
            'integer',
            array(
                'label'    => 'diamante.automation.attributes.weight',
                'required' => true,
            )
        );

        $builder->add(
            'target',
            'text',
            array(
                'label'    => 'diamante.automation.attributes.target',
                'required' => true,
            )
        );

        $builder->add(
            'parent',
            'integer',
            array(
                'label'    => 'diamante.automation.attributes.parent'
            )
        );

        $builder->add(
            'active',
            'integer',
            array(
                'label'    => 'diamante.automation.attributes.active',
                'required' => true,
            )
        );

        $builder->add(
            'expression',
            'text',
            array(
                'label'    => 'diamante.automation.attributes.expression',
                'required' => true,
            )
        );

        $builder->add(
            'mode',
            'text',
            array(
                'label'    => 'diamante.automation.attributes.mode',
                'required' => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Diamante\AutomationBundle\Api\Command\RuleCommand',
                'intention' => 'rule',
                'cascade_validation' => true
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'diamante_rule_form';
    }
}
