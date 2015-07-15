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

use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Class UserRoleListener
 * @package Diamante\DistributionBundle\EventListener
 */
class UserRoleListener
{

    /**
     * @var array
     */
    private static $allowedEntities = array(
        '(root)',
        //'Oro\\Bundle\\AttachmentBundle\\Entity\\Attachment',
        'Diamante\\DeskBundle\\Entity\\Branch',
        //'Oro\\Bundle\\OrganizationBundle\\Entity\\BusinessUnit',
        //'Oro\\Bundle\\CalendarBundle\\Entity\\Calendar',
        //'Oro\\Bundle\\CalendarBundle\\Entity\\CalendarEvent',
        //'Oro\\Bundle\\CommentBundle\\Entity\\Comment',
        'Diamante\\DeskBundle\\Entity\\Comment',
        //'Oro\\Bundle\\DashboardBundle\\Entity\\Dashboard',
        //'Oro\\Bundle\\EmailBundle\\Entity\\Email',
        'Oro\\Bundle\\EmbeddedFormBundle\\Entity\\EmbeddedForm',
        'Oro\\Bundle\\DataGridBundle\\Entity\\GridView',
        'Oro\\Bundle\\UserBundle\\Entity\\Group',
        'Oro\\Bundle\\IntegrationBundle\\Entity\\Channel',
        //'Oro\\Bundle\\NoteBundle\\Entity\\Note',
        'Oro\\Bundle\\NotificationBundle\\Entity\\EmailNotification',
        //'Oro\\Bundle\\OrganizationBundle\\Entity\\Organization',
        //'Oro\\Bundle\\WorkflowBundle\\Entity\\ProcessDefinition',
        //'Oro\\Bundle\\ReportBundle\\Entity\\Report',
        'Oro\\Bundle\\UserBundle\\Entity\\Role',
        //'Oro\\Bundle\\SegmentBundle\\Entity\\Segment',
        //'Oro\\Bundle\\CalendarBundle\\Entity\\SystemCalendar',
        'Oro\\Bundle\\TagBundle\\Entity\\Tag',
        //'Oro\\Bundle\\EmailBundle\\Entity\\EmailTemplate',
        'Diamante\\DeskBundle\\Entity\\Ticket',
        //'Oro\\Bundle\\TrackingBundle\\Entity\\TrackingWebsite',
        'Oro\\Bundle\\UserBundle\\Entity\\User',
        //'Oro\\Bundle\\WorkflowBundle\\Entity\\WorkflowDefinition',
        //'Diamante\\DeskBundle\\Entity\\TicketHistory',
    );

    /**
     * @var array
     */
    private static $allowedActions = array(
        //'oro_dataaudit_history',
        'oro_importexport_export',
        'oro_importexport',
        'oro_importexport_import',
        'oro_jobs',
        //'oro_entityconfig_manage',
        //'oro_system_calendar_event_management',
        //'oro_public_calendar_event_management',
        //'oro_public_calendar_management',
        'password_management',
        'oro_entity_merge',
        //'oro_address_dictionaries_read',
        'oro_search',
        'oro_datagrid_gridview_publish',
        //'oro_platform_system_info',
        'oro_config_system',
        'oro_tag_assign_unassign',
        'oro_tag_unassign_global',
        'oro_datagrid_gridview_edit_public',
        //'oro_workflow',
    );

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $_route = $event->getRequest()->attributes->get('_route');
        if ('oro_user_role_update' === $_route) {
            $result = $event->getControllerResult();
            if (!isset($result['form'])) {
                return;
            }

            $this->filterFormEntities($result['form']);
            $this->filterFormActions($result['form']);

        }
    }

    /**
     * @param FormView $form
     */
    private function filterFormEntities(FormView $form)
    {
        if (!isset($form->children['entity'])) {
            return;
        }

        foreach ($form->children['entity']->children as $key => $entityView) {
            /** @var AclPrivilege $privilege */
            $privilege = $entityView->vars['value'];

            $entityId = $privilege->getIdentity()->getId();
            $entityClass = str_replace('entity:', '', $entityId);

            if (!in_array($entityClass, static::$allowedEntities)) {
                unset ($form->children['entity']->children[$key]);
            }
        }
    }

    /**
     * @param FormView $form
     */
    private function filterFormActions(FormView $form)
    {
        if (!isset($form->children['action'])) {
            return;
        }

        foreach ($form->children['action']->children as $key => $actionView) {
            /** @var AclPrivilege $privilege */
            $privilege = $actionView->vars['value'];

            $actionId = $privilege->getIdentity()->getId();
            $actionName = str_replace('action:', '', $actionId);

            if (!in_array($actionName, static::$allowedActions)) {
                unset ($form->children['action']->children[$key]);
            }
        }
    }

}
