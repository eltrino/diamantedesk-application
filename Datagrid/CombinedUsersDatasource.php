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
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class CombinedUsersDatasource implements DatasourceInterface
{
    const TYPE = 'diamante_combined_users_datasource';
    const DIAMANTE_USERNAME_PLACEHOLDER = '-';
    const DIAMANTE_USER_TYPE_POSTFIX = '[diamante]';
    const ORO_USER_TYPE_POSTFIX = '[oro]';
    const PATH_PAGER_ORIGINAL_TOTALS = '[source][original_totals]';

    /**
     * @var Registry
     */
    protected $doctrineRegistry;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $requestPagerParameters = [
        '_page'     => 1,
        '_per_page' => 25,
    ];

    /**
     * @var DatagridInterface
     */
    protected $grid;

    /**
     * @var int
     */
    protected $originalTotals = 0;

    public function __construct(
        Registry $doctrineRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->doctrineRegistry = $doctrineRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param DatagridInterface $grid
     * @param array $config
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->grid = $grid;

        if ($pagerParameters = $grid->getParameters()->get('_pager')) {
            $this->requestPagerParameters = $pagerParameters;
        }

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

        $rows = $this->applyPagination($rows);

        return $rows;
    }

    /**
     * @return array
     */
    protected function getOroUsers()
    {
        return $this->doctrineRegistry->getManager()->getRepository('OroUserBundle:User')
            ->createQueryBuilder('e')
            ->select('e')
            ->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @return array
     */
    protected function getDiamanteUsers()
    {
        return $this->doctrineRegistry->getManager()->getRepository('DiamanteUserBundle:DiamanteUser')
            ->createQueryBuilder('e')
            ->select('e')
            ->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @param array $rows
     * @return array
     */
    private function applyPagination(array $rows)
    {
        $this->originalTotals = count($rows);
        $this->grid->getConfig()->offsetAddToArrayByPath(static::PATH_PAGER_ORIGINAL_TOTALS, [$this->originalTotals]);

        if (count($rows) > $this->requestPagerParameters['_per_page']) {
            $offset = ($this->requestPagerParameters['_page'] - 1) * $this->requestPagerParameters['_per_page'];
            if ($offset < 0) {
                $offset = 0;
            }
            $rows = array_slice(
                $rows,
                $offset,
                $this->requestPagerParameters['_per_page']
            );
        }

        return $rows;
    }
}
