<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;

class CreateDiamanteUserType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'email',
            EmailType::class,
            [
                'label'         => 'diamante.user.labels.email',
                'required'      => true
            ]
        );

        $builder->add(
            'firstName',
            TextType::class,
            [
                'label'         => 'diamante.user.labels.first_name',
                'required'      => true
            ]
        );

        $builder->add(
            'lastName',
            TextType::class,
            [
                'label'         => 'diamante.user.labels.last_name',
                'required'      => true
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'            => CreateDiamanteUserCommand::class,
                'intention'             => 'diamante_user',
                'cascade_validation'    => true,
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
        return 'diamante_user_create';
    }
}