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
use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Diamante\DeskBundle\Form\DataTransformer\StatusTransformer;
use Diamante\DeskBundle\Api\Command\CommentCommand;
use Symfony\Component\Validator\Constraints\Valid;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statusTransformer = new StatusTransformer();
        $statusOptions = $statusTransformer->getOptions();

        $builder->add(
            $builder->create(
                'ticketStatus',
                ChoiceType::class,
                [
                    'label' => 'diamante.desk.comment.ticket_status',
                    'required' => true,
                    'choices' => $statusOptions,
                ]
            )
                ->addModelTransformer($statusTransformer)
        );

        $builder->add(
            'content',
            OroRichTextType::class,
            [
                'label' => 'diamante.desk.comment.content',
                'required' => true,
            ]
        );

        $builder->add(
            $builder->create(
                'attachmentsInput',
                FileType::class,
                [
                    'label' => 'diamante.desk.attachment.entity_plural_label',
                    'required' => false,
                    'attr' => [
                        'multiple' => 'multiple',
                    ],
                ]
            )->addModelTransformer(new AttachmentTransformer())
        );

        $builder->add(
            'private',
            CheckboxType::class,
            [
                'label' => 'diamante.desk.comment.private',
                'required' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CommentCommand::class,
                'intention' => 'comment',
                'constraints' => new Valid(),
            ]
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'diamante_comment_form';
    }
}
