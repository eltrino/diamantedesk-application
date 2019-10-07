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

namespace Diamante\DistributionBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\Tree\AbstractNodeDefinition;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Class ConfigurationListener
 * @package Diamante\DistributionBundle\EventListener
 */
class ConfigurationListener
{
    /**
     * @var array
     */
    private static $disabledItems = array(
        //'platform',
        //'general_setup',
        //'application_settings',
        //'application_name_settings',
        //'localization',
        //'locale_settings',
        //'map_settings',
        //'language_settings',
        //'language_languages',
        //'look_and_feel',
        //'navbar_settings',
        'activity_list_settings',
        'calendar_settings',
        //'grid_settings',
        //'wysiwyg_settings',
        //'sidebar_settings',
        'tracking',
        'tracking_settings',
        //'email_configuration',
        //'signature_configuration',
        //'email_threads',
        //'email_notifications_settings',
        'attachment_settings',
        'attachment_mime_types_settings',
        //'diamante_desk_setup',
        //'diamante_desk_notifications',
        //'diamante_desk_email_notifications_group',
        //'diamante_desk_channels',
        //'diamante_desk_channels_general_group',
        //'diamante_desk_channels_email_group',
        //'diamantedesk_channels_front_group',
        'integrations',
        'google_settings',
        'google_integration_settings',
        'mailboxes',
    );

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $_route = $event->getRequest()->attributes->get('_route');
        if ('oro_config_configuration_system' !== $_route) {
            return;
        }

        $result = $event->getControllerResult();

        $data = $this->cleanTree($result['data']);
        $form = $result['form'];
        $this->clearForm($form);

        $result['data'] = $data;
        $result['form'] = $form;
        $event->setControllerResult($result);
    }

    /**
     * Recursive function to remove not used config items
     *
     * @param array $node
     * @return array
     */
    public function cleanTree(array $node): array
    {
        foreach ($node as $key => $child) {
            if (in_array($child['id'], static::$disabledItems, true)) {
                unset($node[$key]);
            }
        }
        $node = array_values($node);
        return $node;
    }

    /**
     * Clear configuration form from unused elements
     *
     * @param FormView $form
     */
    public function clearForm(FormView $form)
    {
        foreach ($form->children as $key => $child) {
            if (isset($child->vars['subblock']) && in_array($child->vars['subblock'], static::$disabledItems)) {
                unset(
                    $form->vars['data'][$key],
                    $form->vars['value'][$key],
                    $form->children[$key],
                    $form->vars['block_config'][$form->vars['name']]['subblocks'][$child->vars['subblock']]
                );
            }
        }
    }
}
