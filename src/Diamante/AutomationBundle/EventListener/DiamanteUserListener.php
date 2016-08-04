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

use Diamante\AutomationBundle\Automation\Action\Email\NotifyByEmailAction;
use Diamante\AutomationBundle\Entity\Action;
use Diamante\AutomationBundle\Rule\Action\AbstractModifyAction;
use Diamante\UserBundle\Entity\DiamanteUser;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DiamanteUserListener
 *
 * @package Diamante\AutomationBundle\EventListener
 */
class DiamanteUserListener
{
    /** @var  ContainerInterface */
    protected $container;

    /**
     * OroUserListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $manager = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();


        if (!$entity instanceof DiamanteUser) {
            return;
        }

        $isDeleted = $eventArgs->hasChangedField('isDeleted');
        $isDeletedValue = $eventArgs->getNewValue('isDeleted');

        if (!$isDeleted || !$isDeletedValue) {
            return;
        }

        $workflowActions = $manager->getRepository('DiamanteAutomationBundle:WorkflowAction')->findByType(
            [NotifyByEmailAction::ACTION_NAME]
        );
        $businessActions = $manager->getRepository('DiamanteAutomationBundle:BusinessAction')->findByType(
            [NotifyByEmailAction::ACTION_NAME]
        );

        /** @var Action[] $items */
        $items = array_merge($workflowActions, $businessActions);

        foreach ($items as $item) {
            $parameters = $item->getParameters();

            $email = $parameters[NotifyByEmailAction::ACTION_NAME];
            $user = $manager->getRepository('OroUserBundle:User')->findOneByEmail($email);

            if ($email == $entity->getEmail() && is_null($user)) {
                $parameters['notify_by_email'] = AbstractModifyAction::PROPERTY_REMOVED;
                $item->setParameters($parameters);
                $item->getRule()->deactivate();
                $manager->persist($item);
                $manager->flush();
            }
        }
    }
}
