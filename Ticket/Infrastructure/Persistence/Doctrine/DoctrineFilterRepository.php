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
namespace Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Persistence\Doctrine;

use Eltrino\DiamanteDeskBundle\Entity\Filter;

class DoctrineFilterRepository extends \Doctrine\ORM\EntityRepository
    implements \Eltrino\DiamanteDeskBundle\Ticket\Model\FilterRepository
{
    /**
     * Retrieves Filter by given id
     * @param int $id
     * @return Filter
     */
    public function get($id)
    {
        return $this->find($id);
    }

    /**
     * Retrieves Filters List
     * @return mixed
     */
    public function getAll()
    {
        return $this->findAll();
    }
} 