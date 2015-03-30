<?php

namespace Diamante\DeskBundle\EventListener;

use Diamante\DeskBundle\Entity\DiamanteUser;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DiamanteUserListener implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['onFlush'];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        if(!$this->container->has('orocrm_contact.contact.manager')) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $insertedEntities = $uow->getScheduledEntityInsertions();
        $updatedEntities  = $uow->getScheduledEntityUpdates();

        $entities = array_merge($insertedEntities, $updatedEntities);

        foreach ($entities as $entity) {
            if (!$entity instanceof DiamanteUser) {
                continue;
            }

            $contactManager = $this->container->get('orocrm_contact.contact.manager');
            $contact = $contactManager->getRepository()->findOneBy(['email' => $entity->getEmail()]);

            if (empty($contact)) {
                continue;
            }

            if ($entity->getFirstName() == null) {
                $entity->setFirstName($contact->getFirstName());
            }

            if ($entity->getLastName() == null) {
                $entity->setLastName($contact->getLastName());
            }

            try {
                $em->persist($entity);
                $md = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($md, $entity);
            } catch (\Exception $e) {
                $this->container
                    ->get('monolog.logger.diamante')
                    ->addWarning(
                        sprintf('Error saving Contact Information for Diamante User with email: %s', $entity->getEmail())
                    );
            }
        }
    }

}