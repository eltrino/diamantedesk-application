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
namespace Eltrino\DiamanteDeskBundle\Branch\Infrastructure\Persistence\Doctrine;

use Eltrino\DiamanteDeskBundle\Branch\Model\Branch;

class DoctrineBranchRepository extends \Doctrine\ORM\EntityRepository
    implements \Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository
{
    /**
     * Retrieves all Branches
     * @return Branch[]
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * Retrieves Branch by given id
     * @param $id
     * @return Branch
     */
    public function get($id)
    {
        return $this->find($id);
    }

    /**
     * Store Branch
     *
     * @param Branch $branch
     * @return void
     */
    public function store(Branch $branch)
    {
        $this->getEntityManager()->persist($branch);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete Branch
     * @param Branch $branch
     * @return void
     */
    public function remove(Branch $branch)
    {
        $this->getEntityManager()->remove($branch);
        $this->getEntityManager()->flush();
    }
}
