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
namespace Eltrino\DiamanteDeskBundle\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BranchRemoveEventListener
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $serviceContainer;

    public function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    public function preRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof \Eltrino\DiamanteDeskBundle\Entity\Branch) {
            $systemSettings = $this->serviceContainer->get('diamante.email_processing.mail_system_settings');
            if ($entity->getId() == $systemSettings->getDefaultBranchId()) {
                $message = "You are trying to remove default branch."
                    . " Please, choose other branch as a default one and then back to remove this branch.";
                throw new \RuntimeException(sprintf('%s', $message));
            }
        }
    }
}
