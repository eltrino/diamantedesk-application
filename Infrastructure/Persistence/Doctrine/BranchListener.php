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
namespace Diamante\DeskBundle\Infrastructure\Persistence\Doctrine;

use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Branch\DuplicateBranchKeyException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

class BranchListener
{
    /**
     * @ORM\PrePersist
     * @param Branch $branch
     * @param LifecycleEventArgs $event
     */
    public function prePersistHandler(Branch $branch, LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $query = $em->createQuery("SELECT b FROM DiamanteDeskBundle:Branch b WHERE b.key = :key")
            ->setParameter('key', $branch->getKey());
        $result = $query->getResult();
        if (count($result) > 0) {
            throw new DuplicateBranchKeyException('Branch key already exists. Please, provide another one.');
        }
    }
} 
