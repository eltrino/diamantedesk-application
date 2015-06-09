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

namespace Diamante\DeskBundle\Model\Shared;

use Diamante\DeskBundle\Model\Shared\Filter\PagingProperties;
use Diamante\UserBundle\Model\ApiUser\ApiUser;

interface FilterableRepository
{
    /**
     * @param array $criteria
     * @param PagingProperties $pagingProperties
     * @param ApiUser $user
     * @return Entity[]
     */
    public function filter(array &$criteria, PagingProperties $pagingProperties, $user = null);

    /**
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria);
}