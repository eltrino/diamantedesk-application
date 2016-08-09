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

namespace Diamante\AutomationBundle\Migrations\Data\ORM;

use Diamante\AutomationBundle\Entity\EventTriggeredGroup;
use Diamante\AutomationBundle\Entity\TimeTriggeredAction;
use Diamante\AutomationBundle\Entity\TimeTriggeredGroup;
use Diamante\AutomationBundle\Entity\TimeTriggeredRule;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\EventTriggeredAction;
use Diamante\AutomationBundle\Entity\EventTriggeredRule;
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
            'notifyThatTicketCreated',
            'notifyThatCommentWasAdded',
            'notifyThatTicketStatusChanged',
            'notifyThatTicketReassigned',
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
     * @return EventTriggeredRule
     */
    private function notifyThatCommentWasAdded()
    {
        $rule = new EventTriggeredRule('Notify assignee, reporter, watchers that comment was added', 'comment');
        $group = new EventTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('created', ['created' => 'true'], $group);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
        $rule->addAction($action);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
        $rule->addAction($action);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'watchers'], $rule);
        $rule->addAction($action);
        $rule->setGrouping($group);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return EventTriggeredRule
     */
    private function notifyThatTicketStatusChanged()
    {
        $rule = new EventTriggeredRule('Notify assignee, reporter, watchers that ticket status was changed', 'ticket');
        $group = new EventTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('changed', ['status' => 'changed'], $group);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
        $rule->addAction($action);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
        $rule->addAction($action);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'watchers'], $rule);
        $rule->addAction($action);
        $rule->setGrouping($group);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return EventTriggeredRule
     */
    private function notifyThatTicketCreated()
    {
        $rule = new EventTriggeredRule('Notify reporter, assignee that ticket was created', 'ticket');
        $group = new EventTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('created', ['created' => 'true'], $group);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
        $rule->addAction($action);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
        $rule->addAction($action);
        $rule->setGrouping($group);
        $group->addCondition($condition);

        return $rule;
    }

    /**
     * @return EventTriggeredRule
     */
    private function notifyThatTicketReassigned()
    {
        $rule = new EventTriggeredRule('Notify assignee, watchers that ticket was reassigned', 'ticket');
        $group = new EventTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('changed', ['assignee' => 'changed'], $group);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
        $rule->addAction($action);
        $action = new EventTriggeredAction('notify_by_email', ['notify_by_email' => 'watchers'], $rule);
        $rule->addAction($action);
        $rule->setGrouping($group);
        $group->addCondition($condition);

        return $rule;
    }

//    /**
//     * @return TimeTriggeredRule
//     */
//    private function notifyAssigneeTicketNew24Hours()
//    {
//        $rule = new TimeTriggeredRule('Notify assignee that ticket is new for 24 hours', 'ticket', '24h');
//        $group = new TimeTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
//        $condition = new Condition('eq', ['status' => Status::NEW_ONE], $group);
//        $action = new TimeTriggeredAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
//        $rule->setGrouping($group);
//        $rule->addAction($action);
//        $group->addCondition($condition);
//
//        return $rule;
//    }

//    /**
//     * @return TimeTriggeredRule
//     */
//    private function notifyAssigneeTicketNew3Days()
//    {
//        $rule = new TimeTriggeredRule('Notify assignee that ticket is new for 3 days', 'ticket', '3d');
//        $group = new TimeTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
//        $condition = new Condition('eq', ['status' => Status::NEW_ONE], $group);
//        $action = new TimeTriggeredAction('notify_by_email', ['notify_by_email' => 'assignee'], $rule);
//        $rule->setGrouping($group);
//        $rule->addAction($action);
//        $group->addCondition($condition);
//
//        return $rule;
//    }

    /**
     * @return TimeTriggeredRule
     */
    private function closeTicketWithPendingStatus()
    {
        $rule
            =
        $rule = new TimeTriggeredRule('Auto close ticket with pending status and no update for 30 days', 'ticket', '30d');
        $group = new TimeTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
        $condition = new Condition('gt', ['status_updated_since' => '720'], $group);
        $action = new TimeTriggeredAction('update_property', ['status' => Status::CLOSED], $rule);
        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($condition);

        return $rule;
    }

//    /**
//     * @return TimeTriggeredRule
//     */
//    private function notifyReporterTicketPending7Days()
//    {
//        $rule
//            = $rule = new TimeTriggeredRule(
//            ' If pending for 7 days email reporter that support team waits for response',
//            'ticket',
//            '7d'
//        );
//        $group = new TimeTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
//        $condition = new Condition('eq', ['status' => Status::PENDING], $group);
//        $action = new TimeTriggeredAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
//        $rule->setGrouping($group);
//        $rule->addAction($action);
//        $group->addCondition($condition);
//
//        return $rule;
//    }

//    /**
//     * @return TimeTriggeredRule
//     */
//    private function notifyReporterTicketSupportClose25Days()
//    {
//        $rule
//            = $rule = new TimeTriggeredRule(
//            ' If pending for 25 days email reporter that support team will close ticket soon',
//            'ticket',
//            '25d'
//        );
//        $group = new TimeTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
//        $condition = new Condition('eq', ['status' => Status::PENDING], $group);
//        $action = new TimeTriggeredAction('notify_by_email', ['notify_by_email' => 'reporter'], $rule);
//        $rule->setGrouping($group);
//        $rule->addAction($action);
//        $group->addCondition($condition);
//
//        return $rule;
//    }
}