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

namespace Diamante\DeskBundle\Infrastructure\Ticket\Paging;

use Diamante\UserBundle\Model\User;

/**
 * Class StrategyProvider
 * @package Diamante\DeskBundle\Infrastructure\Ticket\Paging
 */
class StrategyProvider
{
    /**
     * @var array
     */
    protected $mapping = [
        USER::TYPE_DIAMANTE => 'PortalStrategy',
        USER::TYPE_ORO      => 'ApiStrategy',
    ];
    /**
     * @var User
     */
    private $user;
    /**
     * @var AbstractStrategy
     */
    private $strategy;

    /**
     * StrategyProvider constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Strategy
     */
    public function getStrategy()
    {
        if ($this->strategy instanceof Strategy) {
            return $this->strategy;
        }

        $strategyClass = __NAMESPACE__ . '\\' . $this->mapping[$this->user->getType()];

        $this->strategy = new $strategyClass;
        $this->strategy->setUser($this->user);
        return $this->strategy;
    }
}