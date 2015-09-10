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
namespace Diamante\UserBundle\Infrastructure\Persistence\Doctrine;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Model\Entity\Exception\EntityNotFoundException;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Doctrine\ORM\Query;

class DoctrineDiamanteUserRepository extends DoctrineGenericRepository implements DiamanteUserRepository
{
    /**
     * Finds a user by email
     *
     * @param $email
     * @return DiamanteUser
     */
    public function findUserByEmail($email)
    {
        return $this->findOneBy(array('email' => $email));
    }

    /**
     * @param $query
     * @param array $fields
     * @return DiamanteUser[]
     */
    public function searchByInput($query, array $fields)
    {
        $metadata = $this->_em->getClassMetadata($this->_entityName);

        if (!empty($query)) {
            $qb = $this->_em->createQueryBuilder();
            $qb->select('u')->from($this->_entityName, 'u');

            foreach ($fields as $field) {
                if ($metadata->hasField($field)) {
                    $qb->orWhere($qb->expr()->like("u.{$field}", $qb->expr()->literal("%{$query}%")));
                }
            }
            $qb->andWhere($qb->expr()->eq('u.isDeleted', 0));

            $query = $qb->getQuery();

            try {
                $result = $query->getResult(Query::HYDRATE_OBJECT);
            } catch (\Exception $e) {
                //TODO: Log errors
                $result = null;
            }
        } else {
            $result = $this->findBy(['isDeleted' => false]);
        }

        return $result;
    }
}
