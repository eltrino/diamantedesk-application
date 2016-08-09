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
use Diamante\AutomationBundle\Automation\Action\UpdatePropertyAction;
use Diamante\AutomationBundle\Entity\Action;
use Diamante\AutomationBundle\Rule\Action\AbstractModifyAction;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OroUserListener
 *
 * @package Diamante\AutomationBundle\EventListener
 */
class OroUserListener
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
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $manager = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if (!$entity instanceof OroUser) {
            return;
        }

        $eventTriggeredActions = $manager->getRepository('DiamanteAutomationBundle:EventTriggeredAction')->findByType(
            [UpdatePropertyAction::ACTION_NAME, NotifyByEmailAction::ACTION_NAME]
        );
        $timeTriggeredActions = $manager->getRepository('DiamanteAutomationBundle:TimeTriggeredAction')->findByType(
            [UpdatePropertyAction::ACTION_NAME, NotifyByEmailAction::ACTION_NAME]
        );
        $conditions = $manager->getRepository('DiamanteAutomationBundle:Condition')->getAll();

        /** @var Action[] $items */
        $items = array_merge($eventTriggeredActions, $timeTriggeredActions, $conditions);

        foreach ($items as $item) {
            $parameters = $item->getParameters();

            if (array_key_exists('assignee', $parameters)) {
                $user = User::fromString($parameters['assignee']);

                if ($user->getId() == $entity->getId()) {
                    $parameters['assignee'] = AbstractModifyAction::PROPERTY_REMOVED;
                    $item->setParameters($parameters);
                    $item->getRule()->deactivate();
                    $manager->persist($item);
                }
            } elseif (array_key_exists(NotifyByEmailAction::ACTION_NAME, $parameters)) {
                $email = $parameters[NotifyByEmailAction::ACTION_NAME];
                $user = $manager->getRepository('DiamanteUserBundle:DiamanteUser')->findOneByEmail($email);

                if ($email == $entity->getEmail() && is_null($user)) {
                    $parameters['notify_by_email'] = AbstractModifyAction::PROPERTY_REMOVED;
                    $item->setParameters($parameters);
                    $item->getRule()->deactivate();
                    $manager->persist($item);
                }
            }
        }
    }
}
