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

namespace Diamante\DistributionBundle\Routing;


class StringVoter extends Voter
{
    /**
     * @return string
     */
    public function getType()
    {
        return Voter::TYPE_STRING;
    }

    /**
     * @return array
     */
    public function getListedItems()
    {
        return [
            'oro_config_configuration_system',
            'oro_api_get_sidebars',
            'oro_api_cget_sidebarwidgets',
            'oro_default',
            'oro_dashboard_itemized_widget',
            'oro_activity_list_widget_activities',
            'oro_translation_jstranslation',
            'oro_form_autocomplete_search',
            'oro_search_results',
            'oro_pinbar_help',
            'oro_email_user_emails',
            'oro_calendar_view_default',
            'oro_shortcut_actionslist',
            'oro_platform_system_info',
            'oro_datagrid_mass_action',
            'oro_tag_search',

            //OroUserBundle
            'oro_activity_list_api_get_list',
            'oro_comment_form',
            'orocrm_task_user_tasks',

            //Embedded Forms
            'oro_embedded_form_list',
            'oro_embedded_form_create',

            // Api
            'oro_api_get_users',

            //@TODO:REMOVE THESE ON RELEASE
            'oro_email_dashboard_recent_emails',
            'oro_calendar_dashboard_my_calendar',
            'oro_api_get_calendarevents',
            '_wdt',
            'fos_js_routing_js',
            'oro_installer_flow',
            '_imagine_avatar_med'
        ];
    }
}