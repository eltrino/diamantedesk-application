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
use Eltrino\DiamanteDeskBundle\Form\DataTransformer\StatusTransformer;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statusTransformer = new StatusTransformer();
        $statusOptions = $statusTransformer->getOptions();

        $builder->add(
            $builder->create('ticketStatus', 'choice',
                array(
                    'label' => 'Ticket status',
                    'required' => true,
                    'choices' => $statusOptions
                ))
                ->addModelTransformer($statusTransformer)
        );

        $builder->add(
            'content',
            'textarea',
            array(
                'label' => 'Content',
                'required' => true,
            )
        );

        $builder->add(
            'files',
            'file',
            array(
                'label'    => 'Attachments',
                'required' => false,
                'attr' => array(
                    'multiple' => 'multiple'
                )
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
                'data_class' => 'Eltrino\DiamanteDeskBundle\Form\Command\EditCommentCommand',
                'intention' => 'comment',
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
        return 'diamante_comment_form';
    }
}
