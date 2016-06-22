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

use Diamante\DeskBundle\Entity\Ticket;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Diamante\DeskBundle\Loggable\LoggableManager;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

class EntityListener implements OptionalListenerInterface
{
    /**
     * @var LoggableManager
     */
    protected $loggableManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @param LoggableManager    $loggableManager
     * @param ContainerInterface $container
     */
    public function __construct(LoggableManager $loggableManager, ContainerInterface $container)
    {
        $this->loggableManager = $loggableManager;
        $this->container = $container;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        if (!$this->enabled) {
            return;
        }

        $loggedUser = $this->container->get('oro_security.security_facade')->getLoggedUser();
        if ($loggedUser instanceof ApiUser) {
            $this->loggableManager->handleLoggable($event->getEntityManager());
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        if (!$this->enabled) {
            return;
        }

        $loggedUser = $this->container->get('oro_security.security_facade')->getLoggedUser();
        if ($loggedUser instanceof ApiUser) {
            $this->loggableManager->handlePostPersist($event->getEntity(), $event->getEntityManager());
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $manager = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if (!$entity instanceof Ticket) {
            return;
        }

        $remainingTickets = $manager->getRepository('DiamanteDeskBundle:Ticket')->count();

        if (0 === (int)$remainingTickets) {
            $records = $manager->getRepository('DiamanteDeskBundle:TicketTimeline')->getAll();

            foreach ($records as $record) {
                $manager->remove($record);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->reporterEmailSetter($eventArgs);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->reporterEmailSetter($eventArgs);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    protected function reporterEmailSetter(LifecycleEventArgs $eventArgs) {
        $entity = $eventArgs->getEntity();

        if (!$entity instanceof Ticket) {
            return;
        }

        $reporter = $entity->getReporter();
        $user = $this->container->get('diamante.user.service')->getByUser($reporter);
        $entity->setReporterEmail($user->getEmail());
    }
}
