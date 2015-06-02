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

namespace Diamante\DeskBundle\Api;

use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\UserBundle\Model\User;

/**
 * Interface WatchersService
 * @package Diamante\DeskBundle\Api
 * @codeCoverageIgnore
 */
interface WatchersService
{
    /**
     * Create watcher based on ticket and user
     *
     * @param Ticket $ticket
     * @param User $user
     * @return void
     */
    public function addWatcher(Ticket $ticket, User $user);

    /**
     * Remove watcher based on ticket and user
     *
     * @param Ticket $ticket
     * @param User $user
     * @return void
     */
    public function removeWatcher(Ticket $ticket, User $user);

    /**
     * Return watchers list
     *
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getWatchers(Ticket $ticket);
}
