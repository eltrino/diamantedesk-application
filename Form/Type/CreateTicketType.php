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

use Eltrino\DiamanteDeskBundle\Form\DataTransformer\PriorityTransformer;
use Eltrino\DiamanteDeskBundle\Form\DataTransformer\SourceTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Eltrino\DiamanteDeskBundle\Form\DataTransformer\StatusTransformer;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Priority;

class CreateTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'branch',
            'entity',
            array(
                'label' => 'eltrino.diamantedesk.attributes.branch',
                'class' => 'EltrinoDiamanteDeskBundle:Branch',
                'property' => 'name',
                'empty_value' => 'Choose branch...'
            )
        );

        $builder->add(
            'subject',
            'text',
            array(
                'label' => 'eltrino.diamantedesk.attributes.subject',
                'required' => true,
            )
        );

        $builder->add(
            'description',
            'textarea',
            array(
                'label' => 'eltrino.diamantedesk.common.description',
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
                    'label' => 'eltrino.diamantedesk.attributes.status',
                    'required' => true,
                    'choices' => $statusOptions
                ))
                ->addModelTransformer($statusTransformer)
        );

        $builder->add(
            'files',
            'file',
            array(
                'label' => 'eltrino.diamantedesk.attachment.file',
                'required' => true,
                'attr' => array(
                    'multiple' => 'multiple'
                )
            )
        );

        $priorityTransformer = new PriorityTransformer();
        $priorities = $priorityTransformer->getOptions();

        $builder->add(
            $builder->create(
                'priority',
                'choice',
                array(
                    'label'    => 'eltrino.diamantedesk.attributes.priority',
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
                    'label'    => 'eltrino.diamantedesk.attributes.source',
                    'required' => true,
                    'choices'  => $sources,
                )
            )
                ->addModelTransformer($sourceTransformer)
        );

        $builder->add(
            'reporter',
            'oro_user_select',
            array(
                'label'    => 'eltrino.diamantedesk.attributes.reporter',
                'required' => true
            )
        );

        $builder->add(
            'assignee',
            'diamante_assignee_select',
            array(
                'label'    => 'eltrino.diamantedesk.attributes.assignee',
                'required' => false
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
                'data_class'         => 'Eltrino\DiamanteDeskBundle\Form\Command\CreateTicketCommand',
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
