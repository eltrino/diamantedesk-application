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
use Diamante\DeskBundle\Model\Attachment\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Symfony\Component\Validator\Constraints\Valid;

class AttachmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create(
                'attachmentsInput',
                FileType::class,
                array(
                    'label' => 'diamante.desk.attachment.file',
                    'required' => true,
                    'multiple' => true,
                    'attr' => array(
                        'multiple' => true
                    )
                )
            )
                ->addModelTransformer(new AttachmentTransformer())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => AddTicketAttachmentCommand::class,
                'intention' => 'attachment',
                'constraints' => new Valid(),
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
        return $this->getBlockPrefix();
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'diamante_attachment_form';
    }
}
