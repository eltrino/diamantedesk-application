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
namespace Diamante\EmbeddedFormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EmbeddedFormBundle\Form\Type\CustomLayoutFormInterface;

class DiamanteEmbeddedFormType extends AbstractType implements CustomLayoutFormInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'diamante_embedded_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'firstName',
            'text',
            ['required' => true, 'label' => 'First Name']
        );

        $builder->add(
            'lastName',
            'text',
            ['required' => true, 'label' => 'Last Name']
        );

        $builder->add(
            'emailAddress',
            'text',
            ['required' => true, 'label' => 'Email']
        );

        $builder->add(
            'subject',
            'text',
            array(
                'label' => 'diamante.desk.attributes.subject',
                'required' => true,
            )
        );

        $builder->add(
            'description',
            'textarea',
            array(
                'label' => 'diamante.desk.common.description',
                'required' => true,
                'attr'  => array(
                    'class' => 'diam-ticket-description'
                ),
            )
        );

        $builder->add(
            'files',
            'file',
            array(
                'label' => 'diamante.desk.attachment.file',
                'required' => false,
                'attr' => array(
                    'multiple' => 'multiple'
                )
            )
        );

        $builder->add('submit', 'submit');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'        => 'Diamante\DeskBundle\Api\Command\EmbeddedFormCommand',
                'dataChannelField'  => false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormLayout()
    {
        return 'DiamanteEmbeddedFormBundle::embeddedForm.html.twig';
    }
}
