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
namespace Eltrino\EmailProcessingBundle\Model\Processing;

use Eltrino\EmailProcessingBundle\Model\Message;

class CompositeStrategy implements Strategy
{
    private $strategies = array();

    /**
     * @param Strategy $strategy
     */
    public function addStrategy(Strategy $strategy)
    {
        if (!in_array($strategy, $this->strategies)) {
            $this->strategies[] = $strategy;
        }
    }

    /**
     * @param Message $message
     */
    public function process(Message $message)
    {
        foreach ($this->strategies as $strategy) {
            $strategy->process($message);
        }
    }
}
