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

use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Diamante\DeskBundle\Api\Command\BranchCommand;
use Symfony\Component\Validator\Constraints\Valid;

class CreateBranchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            array(
                'label'    => 'diamante.desk.attributes.name',
                'required' => true,
            )
        );

        $builder->add(
            'key',
            TextType::class,
            array(
                'label'    => 'diamante.desk.attributes.key',
                'required' => false,
                'tooltip'  => 'Leave empty to be autogenerated from Branch Name.'
            )
        );

        $builder->add(
            'description',
            OroRichTextType::class,
            array(
                'label'    => 'diamante.desk.common.description',
                'required' => false,
            )
        );

        $builder->add(
            'logoFile',
            FileType::class,
            array(
                'label'    => 'diamante.desk.attachment.image',
                'required' => false,
                'tooltip'  => '"JPEG" and "PNG" image formats are supported only.'
            )
        );

        $builder->add(
            'removeLogo',
            HiddenType::class
        );

        $builder->add(
            'defaultAssignee',
            AssigneeSelectType::class,
            array(
                'label'    => 'diamante.desk.attributes.default_assignee',
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
            array(
                'data_class' => BranchCommand::class,
                'intention' => 'branch',
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

    public function getBlockPrefix()
    {
        return 'diamante_branch_form';
    }
}
