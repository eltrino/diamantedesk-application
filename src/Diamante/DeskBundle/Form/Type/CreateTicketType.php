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
use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Diamante\DeskBundle\Form\DataTransformer\StatusTransformer;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Symfony\Component\Validator\Constraints\Valid;

class CreateTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'branch',
            EntityType::class,
            array(
                'label' => 'diamante.desk.attributes.branch',
                'class' => 'DiamanteDeskBundle:Branch',
                'choice_label' => 'name',
                'placeholder' => 'Choose branch...',
                'required'    => false
            )
        );

        $builder->add(
            'subject',
            TextType::class,
            [
                'label' => 'diamante.desk.attributes.subject',
                'required' => true,
            ]
        );

        $builder->add(
            'description',
            OroRichTextType::class,
            [
                'label' => 'diamante.desk.common.description',
                'required' => true,
                'attr' => [
                    'class' => 'diam-ticket-description',
                ],
            ]
        );

        $statusTransformer = new StatusTransformer();
        $statusOptions = $statusTransformer->getOptions();

        $builder->add(
            $builder->create(
                'status',
                ChoiceType::class,
                [
                    'label' => 'diamante.desk.attributes.status',
                    'required' => true,
                    'choices' => $statusOptions,
                ]
            )
                ->addModelTransformer($statusTransformer)
        );

        $builder->add(
            $builder->create(
                'attachmentsInput',
                FileType::class,
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
                ChoiceType::class,
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
                ChoiceType::class,
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
            ReporterSelectType::class,
            array(
                'label'    => 'diamante.desk.attributes.reporter',
                'required' => true
            )
        );

        $builder->add(
            'assignee',
            AssigneeSelectType::class,
            array(
                'label'    => 'diamante.desk.attributes.assignee',
                'required' => false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CreateTicketCommand::class,
                'intention' => 'ticket',
                'constraints' => new Valid(),
            ]
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'diamante_ticket_form';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

}
