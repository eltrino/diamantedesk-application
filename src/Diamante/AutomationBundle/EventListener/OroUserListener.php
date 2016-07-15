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
namespace Diamante\AutomationBundle\EventListener;

use Diamante\AutomationBundle\Automation\Action\UpdatePropertyAction;
use Diamante\AutomationBundle\Entity\Action;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class OroUserListener
{
    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $manager = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if (!$entity instanceof OroUser) {
            return;
        }

        $workflowActions = $manager->getRepository('DiamanteAutomationBundle:WorkflowAction')->findByType(
            [UpdatePropertyAction::ACTION_NAME]
        );
        $businessActions = $manager->getRepository('DiamanteAutomationBundle:BusinessAction')->findByType(
            [UpdatePropertyAction::ACTION_NAME]
        );

        /** @var Action[] $actions */
        $actions = array_merge($workflowActions, $businessActions);

        foreach ($actions as $action) {
            $parameters = $action->getParameters();

            if (array_key_exists('assignee', $parameters)) {
                $user = User::fromString($parameters['assignee']);

                if ($user->getId() == $entity->getId()) {
                    $parameters['assignee'] = UpdatePropertyAction::UNASSIGNED;
                    $action->setParameters($parameters);
                    $manager->persist($action);
                }
            }
        }
    }
}
