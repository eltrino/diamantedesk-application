<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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


use Diamante\AutomationBundle\Automation\JobQueue\QueueManager;
use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Entity\PersistentProcessingContext;
use Diamante\AutomationBundle\Infrastructure\Changeset\ChangesetBuilder;
use Diamante\DeskBundle\Entity\Comment;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WorkflowListener
{
    const CREATED = 'created';
    const UPDATED = 'updated';
    const REMOVED = 'removed';

    const TICKET_TARGET = 'ticket';
    const COMMENT_TARGET = 'comment';

    /**
     * @var AutomationConfigurationProvider
     */
    protected $provider;

    /**
     * @var QueueManager
     */
    protected $queueManager;

    /**
     * @var ChangesetBuilder
     */
    protected $changesetBuilder;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * WorkflowListener constructor.
     *
     * @param AutomationConfigurationProvider $provider
     * @param QueueManager                    $manager
     * @param ChangesetBuilder                $changesetBuilder
     * @param ContainerInterface              $container
     */
    public function __construct(
        AutomationConfigurationProvider $provider,
        QueueManager $manager,
        ChangesetBuilder $changesetBuilder,
        ContainerInterface $container
    )
    {
        $this->provider = $provider;
        $this->queueManager = $manager;
        $this->changesetBuilder = $changesetBuilder;
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handle($args, static::CREATED, function($entity) {
            return $this->changesetBuilder->getChangesetForCreateAction($entity);
        });
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handle($args, static::UPDATED, function($entity) {
            return $this->changesetBuilder->getChangesetForUpdateAction($entity);
        });
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->handle($args, static::REMOVED, function($entity) {
            return $this->changesetBuilder->getChangesetForRemoveAction($entity);
        });
    }

    /**
     * @param LifecycleEventArgs $args
     * @param                    $action
     * @param callable|null      $getChangeset
     */
    protected function handle(LifecycleEventArgs $args, $action, $getChangeset)
    {
        $entity = $args->getObject();
        $target = $this->provider->getTargetByEntity($entity);

        if (empty($target)) {
            return;
        }

        $em = $args->getEntityManager();

        $processingContext = new PersistentProcessingContext(
            $entity->getId(),
            $this->provider->getClassByTarget($target),
            $action,
            $this->getEditor($entity, $target),
            $getChangeset($entity)
        );

        $disableListeners = true;

        // don't disable listeners if you edit both comment and ticket status on create action
        if (static::COMMENT_TARGET == $target) {
            $changeset = $em->getUnitOfWork()->getEntityChangeSet($entity->getTicket());

            if (isset($changeset['status'])) {
                $disableListeners = false;
            }
        }

        // don't disable listeners when you save comment after ticket status and comment content update on update action
        // don't disable tickets remove for mass action
        if (static::TICKET_TARGET == $target && in_array($action, [static::UPDATED, static::REMOVED])) {
            $disableListeners = false;
        }

        if ($disableListeners) {
            $em->getEventManager()->disableListeners();
        }

        $this->queueManager->setEntityManager($em);
        $this->queueManager->push($processingContext);
    }

    /**
     * @param $entity
     * @param $type
     *
     * @return User
     */
    private function getEditor($entity, $type)
    {
        $user = $this->container->get('oro_security.security_facade')->getLoggedUser();

        if (is_null($user)) {
            if (static::TICKET_TARGET == $type) {
                /** @var Ticket $entity */
                $editor = $entity->getReporter();
            } else {
                /** @var Comment $entity */
                $editor = $entity->getAuthor();
            }
        } else {
            $editor = User::fromEntity($user);
        }

        return $editor;
    }
}
