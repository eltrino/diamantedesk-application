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

use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormType;
use Oro\Component\Layout\LayoutItemInterface;
use Oro\Component\Layout\LayoutManipulatorInterface;
use Oro\Component\Layout\LayoutUpdateInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface;

use Diamante\DeskBundle\Form\DataTransformer\AttachmentTransformer;

class DiamanteEmbeddedFormType extends AbstractType implements EmbeddedFormInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
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
            TextType::class,
            ['required' => true, 'label' => 'First Name']
        );

        $builder->add(
            'lastName',
            TextType::class,
            ['required' => true, 'label' => 'Last Name']
        );

        $builder->add(
            'emailAddress',
            EmailType::class,
            ['required' => true, 'label' => 'Email']
        );

        $builder->add(
            'subject',
            TextType::class,
            array(
                'label' => 'diamante.desk.attributes.subject',
                'required' => true,
            )
        );

        $builder->add(
            'description',
            TextareaType::class,
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
                FileType::class,
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

        $builder->add('submit', SubmitType::class);
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
.row-group input[type="email"],
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

.row-group input[type="text"],
.row-group input[type="email"] {
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
#file-box {
    margin-top: 15px;
    box-sizing: border-box;
    color: rgb(68, 68, 68);
    cursor: default;
    float: left;
    height: 28px;
    position: relative;
    vertical-align: middle;
    width: 294.375px;
    perspective-origin: 147.1875px 14px;
    transform-origin: 147.1875px 14px;
    background: rgb(255, 255, 255) none repeat scroll 0% 0% / auto padding-box border-box;
    border: 1px solid rgb(204, 204, 204);
    border-radius: 3px 3px 3px 3px;
    font: normal normal normal normal 13px/28px Helvetica, Arial, sans-serif;
    outline: rgb(68, 68, 68) none 0px;
    overflow: hidden;
}/*#file-box*/

#file-box label {
    display: none;
}

#diamante_embedded_form_attachmentsInput {
    bottom: 0px;
    box-sizing: content-box;
    color: rgb(51, 51, 51);
    cursor: default;
    display: block;
    height: 25px;
    opacity: 0;
    position: absolute;
    right: 0px;
    top: 0px;
    vertical-align: middle;
    width: 246px;
    z-index: 0;
    align-items: baseline;
    align-self: stretch;
    perspective-origin: 123px 12.5px;
    transform-origin: 123px 12.5px;
    background: rgba(0, 0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box;
    border: 0px none rgb(51, 51, 51);
    font: normal normal normal normal 13px/30px 'Helvetica Neue', Helvetica, Arial, sans-serif;
    outline: rgb(51, 51, 51) none 0px;
    padding: 0px;
}/*#diamante_embedded_form_attachmentsInput*/

#file-title-first {
    box-sizing: border-box;
    color: rgb(68, 68, 68);
    cursor: default;
    display: block;
    float: left;
    height: 28px;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 202px;
    perspective-origin: 101px 14px;
    transform-origin: 101px 14px;
    border: 0px none rgb(68, 68, 68);
    font: normal normal normal normal 13px/28px Helvetica, Arial, sans-serif;
    outline: rgb(68, 68, 68) none 0px;
    overflow: hidden;
    padding: 0px 10px;
}/*#file-title-first*/

#file-title-second {
    box-sizing: border-box;
    color: rgb(68, 68, 68);
    cursor: pointer;
    display: block;
    float: left;
    height: 28px;
    width: 90.375px;
    perspective-origin: 45.1875px 14px;
    transform-origin: 45.1875px 14px;
    background: rgb(245, 245, 245) linear-gradient(rgb(255, 255, 255), rgb(230, 230, 230)) repeat-x scroll 0% 0% / auto padding-box border-box;
    border-top: 0px none rgb(68, 68, 68);
    border-right: 0px none rgb(68, 68, 68);
    border-bottom: 0px none rgb(68, 68, 68);
    border-left: 1px solid rgb(204, 204, 204);
    font: normal normal normal normal 13px/28px Helvetica, Arial, sans-serif;
    outline: rgb(68, 68, 68) none 0px;
    overflow: hidden;
    padding: 0px 10px;
}/*#file-title-second*/
CSS;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSuccessMessage()
    {
        return '<p>Ticket has been placed successfully.</p>{back_link}';
    }
}
