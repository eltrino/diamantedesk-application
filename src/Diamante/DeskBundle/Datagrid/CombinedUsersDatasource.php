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
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class CombinedUsersDatasource extends AbstractDatasource
{
    const TYPE = 'diamante_combined_users_datasource';
    const DIAMANTE_USERNAME_PLACEHOLDER = '-';
    const DIAMANTE_USER_TYPE_POSTFIX = '[diamante]';
    const ORO_USER_TYPE_POSTFIX = '[oro]';

    /** @var QueryBuilder */
    protected $qbDiamanteUsers;

    /** @var QueryBuilder */
    protected $qbOroUsers;

    public function __construct(
        Registry $doctrineRegistry
    ) {
        $this->doctrineRegistry = $doctrineRegistry;

        $this->qbOroUsers = $this->doctrineRegistry->getManager()->getRepository('OroUserBundle:User')
            ->createQueryBuilder('u');
        $this->qbOroUsers->select('u');

        $this->qbDiamanteUsers = $this->doctrineRegistry->getManager()->getRepository('DiamanteUserBundle:DiamanteUser')
            ->createQueryBuilder('u');
        $this->qbDiamanteUsers->select('u');
    }

    /**
     * @return ResultRecordInterface[]
     */
    public function getResults()
    {
        $rows = [];

        $diamanteUsers = $this->getQbDiamanteUsers()->getQuery()->getResult(Query::HYDRATE_ARRAY);
        foreach ($diamanteUsers as $result) {
            $result['id'] = User::TYPE_DIAMANTE . User::DELIMITER . $result['id'];
            $result['username'] = self::DIAMANTE_USERNAME_PLACEHOLDER;
            $result['enabled'] = true;
            $result['email'] = $result['email'] . ' ' . self::DIAMANTE_USER_TYPE_POSTFIX;
            $rows[] = $result;
        }

        $oroUsers = $this->getQbOroUsers()->getQuery()->getResult(Query::HYDRATE_ARRAY);
        foreach ($oroUsers as $result) {
            $result['id'] = User::TYPE_ORO . User::DELIMITER . $result['id'];
            $result['email'] = $result['email'] . ' ' . self::ORO_USER_TYPE_POSTFIX;
            $rows[] = $result;
        }

        $this->applySorting($rows);

        foreach ($rows as $key => $row) {
            $rows[$key] = new ResultRecord($row);
        }

        $rows = $this->applyPagination($rows);

        return $rows;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQbOroUsers()
    {
        return $this->qbOroUsers;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQbDiamanteUsers()
    {
        return $this->qbDiamanteUsers;
    }
    
    /**
     * @return array
     */
    public function getQueryBuilders()
    {
        return [
            $this->getQbDiamanteUsers(),
            $this->getQbOroUsers()
        ];
    }
}
