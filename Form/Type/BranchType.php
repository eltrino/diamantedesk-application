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
namespace Eltrino\DiamanteDeskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BranchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'label'    => 'eltrino.diamantedesk.attributes.name',
                'required' => true,
            )
        );

        $builder->add(
            'description',
            'textarea',
            array(
                'label'    => 'eltrino.diamantedesk.common.description',
                'required' => false,
            )
        );

        $builder->add(
            'logoFile',
            'file',
            array(
                'label'    => 'eltrino.diamantedesk.attachment.image',
                'required' => false,
                'tooltip' => '"JPEG" and "PNG" image formats are supported only.'
            )
        );

        $builder->add(
            'defaultAssignee',
            'diamante_assignee_select',
            array(
                'label'    => 'eltrino.diamantedesk.attributes.assignee',
                'required' => false
            )
        );

        // tags
        $builder->add(
            'tags',
            'oro_tag_select',
            array(
                'label' => 'oro.tag.entity_plural_label'
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
                'data_class' => 'Eltrino\DiamanteDeskBundle\Branch\Api\Command\BranchCommand',
                'intention' => 'branch',
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
        return 'diamante_branch_form';
    }
}
