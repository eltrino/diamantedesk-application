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
namespace Diamante\DeskBundle\Infrastructure\Persistence;

use Doctrine\ORM\EntityRepository;
use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Shared\Repository;

class DoctrineGenericRepository extends EntityRepository implements Repository
{
    /**
     * @param $id
     * @return Entity
     */
    public function get($id)
    {
        return $this->find($id);
    }

    /**
     * @return Entity[]
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param Entity $entity
     * @return void
     */
    public function store(Entity $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    /**
     * @param Entity $entity
     * @return void
     */
    public function remove(Entity $entity)
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }
}
