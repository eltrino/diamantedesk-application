<?php
/*
 * Copyright (c) 2016 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\DataFixtures\ORM;

use Diamante\AutomationBundle\Entity\BusinessAction;
use Diamante\AutomationBundle\Entity\BusinessRule;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\WorkflowAction;
use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\DeskBundle\Model\Ticket\Status;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;

/**
 * Class CreatePredefinedRules
 *
 * @package Diamante\AutomationBundle\DataFixtures\ORM
 */
class CreatePredefinedRules extends ContainerAwareFixture
{
    private $rules
        = [
            'NotifyReporterTicketWasOpen',
            'notifyReporterCommentWasAdded',
            'notifyAssigneeCommentWasAdded',
            'notifyWatchersCommentWasAdded',
            'notifyReporterTicketStatusChanged',
            'notifyAssigneeTicketStatusChanged',
            'notifyWatchersTicketStatusChanged',
            'notifyAssigneeTicketCreated',
            'notifyAssigneeTicketReassigned',
            'notifyWatchersTicketReassigned',
//            'notifyAssigneeTicketNew24Hours',
//            'notifyAssigneeTicketNew3Days',
            'closeTicketWithPendingStatus',
//            'notifyReporterTicketPending7Days',
//            'notifyReporterTicketSupportClose25Days'
        ];

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        try {
            foreach ($this->rules as $rule) {
                $rule = $this->$rule();
                $manager->persist($rule);
            }

            $manager->flush();

        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')
                ->error("Creating predefined rules failed. Reason: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @return WorkflowRule
     */
    private function NotifyReporterTicketWasOpen()
    {
        $rule = new WorkflowRule('Notify reporter that ticket was open', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('eq', ['status' => Status::OPEN], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyReporterCommentWasAdded()
    {
        $rule = new WorkflowRule('Notify reporter that comment was added', 'comment');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('created', ['created' => 'true'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyAssigneeCommentWasAdded()
    {
        $rule = new WorkflowRule('Notify assignee that comment was added', 'comment');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('created', ['created' => 'true'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyWatchersCommentWasAdded()
    {
        $rule = new WorkflowRule('Notify watchers that comment was added', 'comment');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('created', ['created' => 'true'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'watchers'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyReporterTicketStatusChanged()
    {
        $rule = new WorkflowRule('Notify reporter that ticket status was changed', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('changed', ['status' => 'changed'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyAssigneeTicketStatusChanged()
    {
        $rule = new WorkflowRule('Notify assignee that ticket status was changed', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('changed', ['status' => 'changed'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyWatchersTicketStatusChanged()
    {
        $rule = new WorkflowRule('Notify watchers that ticket status was changed', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('changed', ['status' => 'changed'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'watchers'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyAssigneeTicketCreated()
    {
        $rule = new WorkflowRule('Notify assignee that ticket was created', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('created', ['created' => 'true'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyAssigneeTicketReassigned()
    {
        $rule = new WorkflowRule('Notify assignee that ticket was reassigned', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('changed', ['assignee' => 'changed'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return WorkflowRule
     */
    private function notifyWatchersTicketReassigned()
    {
        $rule = new WorkflowRule('Notify watchers that ticket was reassigned', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('changed', ['assignee' => 'changed'], $group);
        $action = new WorkflowAction('notify_by_email', ['notify_by_email' => 'watchers'], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

//    /**
//     * @return BusinessRule
//     */
//    private function notifyAssigneeTicketNew24Hours()
//    {
//        $rule = new BusinessRule('Notify assignee that ticket is new for 24 hours', 'ticket', '24h');
//        $group = new Group(Group::CONNECTOR_INCLUSIVE);
//        $condition = new Condition('eq', ['status' => Status::NEW_ONE], $group);
//        $action = new BusinessAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
//        $rule->setGrouping($group);
//        $rule->addAction($action);
//        $group->addCondition($condition);
//
//        return $rule;
//    }

//    /**
//     * @return BusinessRule
//     */
//    private function notifyAssigneeTicketNew3Days()
//    {
//        $rule = new BusinessRule('Notify assignee that ticket is new for 3 days', 'ticket', '3d');
//        $group = new Group(Group::CONNECTOR_INCLUSIVE);
//        $condition = new Condition('eq', ['status' => Status::NEW_ONE], $group);
//        $action = new BusinessAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
//        $rule->setGrouping($group);
//        $rule->addAction($action);
//        $group->addCondition($condition);
//
//        return $rule;
//    }

    /**
     * @return BusinessRule
     */
    private function closeTicketWithPendingStatus()
    {
        $rule
            =
        $rule = new BusinessRule('Auto close ticket with pending status and no update for 30 days', 'ticket', '30d');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('gt', ['status_updated_since' => '30d'], $group);
        $action = new BusinessAction('update_property', ['status' => Status::CLOSED], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

//    /**
//     * @return BusinessRule
//     */
//    private function notifyReporterTicketPending7Days()
//    {
//        $rule
//            = $rule = new BusinessRule(
//            ' If pending for 7 days email reporter that support team waits for response',
//            'ticket',
//            '7d'
//        );
//        $group = new Group(Group::CONNECTOR_INCLUSIVE);
//        $condition = new Condition('eq', ['status' => Status::PENDING], $group);
//        $action = new BusinessAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
//        $rule->setGrouping($group);
//        $rule->addAction($action);
//        $group->addCondition($condition);
//
//        return $rule;
//    }

//    /**
//     * @return BusinessRule
//     */
//    private function notifyReporterTicketSupportClose25Days()
//    {
//        $rule
//            = $rule = new BusinessRule(
//            ' If pending for 25 days email reporter that support team will close ticket soon',
//            'ticket',
//            '25d'
//        );
//        $group = new Group(Group::CONNECTOR_INCLUSIVE);
//        $condition = new Condition('eq', ['status' => Status::PENDING], $group);
//        $action = new BusinessAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
//        $rule->setGrouping($group);
//        $rule->addAction($action);
//        $group->addCondition($condition);
//
//        return $rule;
//    }
}