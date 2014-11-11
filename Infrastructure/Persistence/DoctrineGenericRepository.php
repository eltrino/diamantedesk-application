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
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;

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
     * Store Entity
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

    /**
     * @param array $conditions
     * @return ArrayCollection|null
     * @throws \Exception
     */
    public function filter(array $conditions)
    {
        $collection = new ArrayCollection($this->getAll());

        $criteria = Criteria::create();
        $allowedConstraints = $this->getAllowedFilteringConstraints();

        foreach ($conditions as $rule) {
            list($field, $constraint, $value) = $rule;

            if (!in_array($constraint, $allowedConstraints)) {
                throw new \Exception(
                    sprintf("Invalid filtering constraint '%s' used. Should be one of these: %s", $constraint, join(', ', $allowedConstraints))
                );
            }

            if (empty($criteria->getWhereExpression())) {
                $criteria->where(Criteria::expr()->$constraint($field, $value));
            } else {
                $criteria->andWhere(Criteria::expr()->$constraint($field, $value));
            }
        }

        $criteria->setFirstResult(0);

        $result = $collection->matching($criteria);

        return $result;
    }

    /**
     * @return array
     */
    protected function getAllowedFilteringConstraints()
    {
        return array(
            'andX', 'orX', 'eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'isNull', 'in', 'notIn', 'contains'
        );
    }
}
