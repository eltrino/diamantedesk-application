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
namespace Diamante\DeskBundle\Datagrid;

use Diamante\UserBundle\Model\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class CombinedUsersDatasource implements DatasourceInterface
{
    const DIAMANTE_USERNAME_PLACEHOLDER = '-';
    const DIAMANTE_USER_TYPE_POSTFIX = '[diamante]';
    const ORO_USER_TYPE_POSTFIX = '[oro]';


    /** @var EntityManager */
    protected $em;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param DatagridInterface $grid
     * @param array $config
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $grid->setDatasource(clone $this);
    }

    /**
     * @return ResultRecordInterface[]
     */
    public function getResults()
    {
        $rows = [];

        foreach ($this->getDiamanteUsers() as $result) {
            $result['id'] = User::TYPE_DIAMANTE . User::DELIMITER . $result['id'];
            $result['username'] = self::DIAMANTE_USERNAME_PLACEHOLDER;
            $result['enabled'] = true;
            $result['email'] = $result['email'] . ' ' . self::DIAMANTE_USER_TYPE_POSTFIX;
            $rows[] = new ResultRecord($result);
        }

        foreach ($this->getOroUsers() as $result) {
            $result['id'] = User::TYPE_ORO . User::DELIMITER . $result['id'];
            $result['email'] = $result['email'] . ' ' . self::ORO_USER_TYPE_POSTFIX;
            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }

    /**
     * @return array
     */
    protected function getOroUsers()
    {
        return $this->em->getRepository('OroUserBundle:User')
            ->createQueryBuilder('e')
            ->select('e')
            ->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @return array
     */
    protected function getDiamanteUsers()
    {
        return $this->em->getRepository('DiamanteUserBundle:DiamanteUser')
            ->createQueryBuilder('e')
            ->select('e')
            ->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }
}
