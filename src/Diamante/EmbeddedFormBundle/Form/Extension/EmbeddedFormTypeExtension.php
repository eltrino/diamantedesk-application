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
namespace Diamante\EmbeddedFormBundle\Form\Extension;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\FormBundle\Utils\FormUtils;

class EmbeddedFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'embedded_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modifier = function(FormEvent $event) {
            $form = $event->getForm();

            if ($form->has('additional') && $form->get('additional')->has('branch')) {
                FormUtils::replaceField(
                    $form->get('additional'),
                    'branch',
                    ['required' => true, 'constraints' => [new NotBlank()]]
                );
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $modifier);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $modifier);
    }
}
