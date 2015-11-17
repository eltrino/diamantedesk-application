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
namespace Diamante\DeskBundle\EventListener;

use Diamante\UserBundle\Entity\DiamanteUser;
use Oro\Bundle\UserBundle\Entity\User;
use Diamante\UserBundle\Model\User as UserModel;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OroUserSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'preRemove'
        ];
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof User || $entity instanceof DiamanteUser) {
            $type = $entity instanceof User ? UserModel::TYPE_ORO : UserModel::TYPE_DIAMANTE;
            $user = new UserModel($entity->getId(), $type);
            $this->container->get('diamante.user_cleanup.service')->cleanupUser($user);
        }
    }
}