<?php

namespace Diamante\DeskBundle\Datagrid;


use Doctrine\ORM\EntityRepository;

class BranchHelper
{
    /**
     * Returns query builder callback for branch filter form type
     *
     * @return callable
     */
    public function getBranchFilterQueryBuilder()
    {
        return function (EntityRepository $er) {
            return $er->createQueryBuilder('c')
                ->orderBy('c.name', 'ASC');
        };
    }
}