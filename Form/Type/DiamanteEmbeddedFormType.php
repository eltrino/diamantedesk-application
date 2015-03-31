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

use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface;
use Oro\Bundle\EmbeddedFormBundle\Form\Type\CustomLayoutFormInterface;

use Diamante\DeskBundle\Form\DataTransformer\AttachmentTransformer;

class DiamanteEmbeddedFormType extends AbstractType implements EmbeddedFormInterface, CustomLayoutFormInterface
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
            'email',
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
            $builder->create(
                'attachmentsInput',
                'file',
                array(
                    'label' => 'diamante.desk.attachment.file',
                    'required' => false,
                    'attr' => array(
                        'multiple' => 'multiple',
                    )
                )
            )
            ->addModelTransformer(new AttachmentTransformer())
        );

        $builder->add('submit', 'submit');
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDefaultCss()
    {
        return <<<CSS
body {
    margin: 0;
    color: #000;
    min-width: 320px;
    background: #fff;
    font: 13px/18px Arial, Helvetica, sans-serif;
}

#page div {
    box-sizing: content-box;
    -moz-box-sizing: content-box;
    -webkit-box-sizing: content-box;
}

#page {
    padding: 0 40px;
}

.row-group {
    width: 100%;
}

.row-group:after {
    content: "";
    display: block;
    clear: both;
}

.row-group label {
    display: block;
    clear: both;
    font-weight: normal;
    margin: 0 0 3px;
}

.row-group label em {
    color: #f00;
    font-size: 16px;
}

.row-group .box {
    display: inline-block;
    width: 48.5%;
    min-width: 410px;
    margin: 0 0 5px;
}

.row-group .box:first-child {
    padding-right: 2%;
}

.row-group input[type="text"],
.row-group textarea,
.row-group button {
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 3px;
    -webkit-border-radius: 3px;
    font-size: 12px;
    color: #000;
    background-color: #fff;
}

.row-group input[type="text"] {
    display: block;
    width: 100%;
    height: 26px;
    line-height: 26px;
    padding: 0 10px;
}

.row-group textarea {
    font-size: 12px;
    display: block;
    min-width: 410px;
    width: 99.5%;
    min-height: 75px;
    resize: vertical;
}

.row-group button {
    font: 13px/24px Arial, Helvetica, sans-serif;
    height: 28px;
    background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#ffffff), to(#f1f0f0));
    background: -webkit-linear-gradient(top, #ffffff, #f1f0f0);
    background: -moz-linear-gradient(top, #ffffff, #f1f0f0);
    background: -ms-linear-gradient(top, #ffffff, #f1f0f0);
    background: -o-linear-gradient(top, #ffffff, #f1f0f0);
    padding: 0 25px;
    margin-top: 10px;
}
span.validation-failed {
    color: #c81717;
    display: block;
    line-height: 1.1em;
    margin: 3px 0 6px 0;
}
CSS;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSuccessMessage()
    {
        return '<p>Ticket has been placed successfully.</p>{back_link}';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormLayout()
    {
        return 'DiamanteEmbeddedFormBundle::embeddedForm.html.twig';
    }
}
