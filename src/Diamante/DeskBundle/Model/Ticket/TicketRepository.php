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
namespace Diamante\DeskBundle\Model\Ticket;

use Diamante\DeskBundle\Model\Shared\FilterableRepository;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Model\Shared\Filter\PagingProperties;
use Diamante\UserBundle\Model\User;

/**
 * Interface TicketRepository
 *
 * @package Diamante\DeskBundle\Model\Ticket
 */
interface TicketRepository extends Repository, FilterableRepository
{
    /**
     * Find Ticket by given id without private comments
     * @param int $id
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function getByTicketIdWithoutPrivateComments($id);

    /**
     * Find Ticket by given TicketKey
     * @param TicketKey $key
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function getByTicketKey(TicketKey $key);

    /**
     * Find Ticket by given TicketKey without private comments
     * @param TicketKey $key
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function getByTicketKeyWithoutPrivateComments(TicketKey $key);

    /**
     * @param UniqueId $uniqueId
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function getByUniqueId(UniqueId $uniqueId);

    /**
     * Remove reporter id from ticket table
     * @param User $user
     */
    public function removeTicketReporter(User $user);

    /**
     * Search ticket by subject and description
     *
     * @param string $searchQuery
     * @param array $conditions
     * @param PagingProperties $pagingProperties
     * @param null $callback
     * @return \Diamante\DeskBundle\Entity\Ticket[]
     */
    public function search($searchQuery, array $conditions, PagingProperties $pagingProperties, $callback = null);

}
