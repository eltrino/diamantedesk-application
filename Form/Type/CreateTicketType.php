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

use Diamante\DeskBundle\Form\DataTransformer\AttachmentTransformer;
use Diamante\DeskBundle\Form\DataTransformer\PriorityTransformer;
use Diamante\DeskBundle\Form\DataTransformer\SourceTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Diamante\DeskBundle\Form\DataTransformer\StatusTransformer;

class CreateTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'branch',
            'entity',
            array(
                'label' => 'diamante.desk.attributes.branch',
                'class' => 'DiamanteDeskBundle:Branch',
                'property' => 'name',
                'empty_value' => 'Choose branch...'
            )
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
            'oro_rich_text',
            array(
                'label' => 'diamante.desk.common.description',
                'required' => true,
                'attr'  => array(
                    'class' => 'diam-ticket-description'
                ),
            )
        );

        $statusTransformer = new StatusTransformer();
        $statusOptions = $statusTransformer->getOptions();

        $builder->add(
            $builder->create('status', 'choice',
                array(
                    'label' => 'diamante.desk.attributes.status',
                    'required' => true,
                    'choices' => $statusOptions
                ))
                ->addModelTransformer($statusTransformer)
        );

        $builder->add(
            $builder->create(
                'attachmentsInput',
                'file',
                array(
                    'label' => 'diamante.desk.attachment.file',
                    'required' => false,
                    'attr' => array(
                        'multiple' => 'multiple'
                    )
                )
            )->addModelTransformer(new AttachmentTransformer())
        );

        $priorityTransformer = new PriorityTransformer();
        $priorities = $priorityTransformer->getOptions();

        $builder->add(
            $builder->create(
                'priority',
                'choice',
                array(
                    'label'    => 'diamante.desk.attributes.priority',
                    'required' => true,
                    'choices'  => $priorities,
                )
            )
            ->addModelTransformer($priorityTransformer)
        );

        $sourceTransformer = new SourceTransformer();
        $sources = $sourceTransformer->getOptions();

        $builder->add(
            $builder->create(
                'source',
                'choice',
                array(
                    'label'    => 'diamante.desk.attributes.source',
                    'required' => true,
                    'choices'  => $sources,
                    'preferred_choices' => ['web'],
                )
            )
                ->addModelTransformer($sourceTransformer)
        );

        $builder->add(
            'reporter',
            'diamante_reporter_select',
            array(
                'label'    => 'diamante.desk.attributes.reporter',
                'required' => true
            )
        );

        $builder->add(
            'assignee',
            'diamante_assignee_select',
            array(
                'label'    => 'diamante.desk.attributes.assignee',
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
                'data_class'         => 'Diamante\DeskBundle\Api\Command\CreateTicketCommand',
                'intention'          => 'ticket',
                'cascade_validation' => true,
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
        return 'diamante_ticket_form';
    }
}
