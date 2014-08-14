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
namespace Eltrino\EnailProcessingBundle\Tests\Model\Processing;

use Eltrino\EmailProcessingBundle\Model\Processing\StrategyHolder;
use Eltrino\EmailProcessingBundle\Model\Processing\Strategy;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class StrategyHolderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StrategyHolder
     */
    private $strategyHolder;

    /**
     * @var \Eltrino\EmailProcessingBundle\Model\Processing\Strategy
     * @Mock \Eltrino\EmailProcessingBundle\Model\Processing\Strategy
     */
    private $strategy;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->strategyHolder = new StrategyHolder();
    }

    public function testGetStrategies()
    {
        $strategies = array($this->strategy);

        $this->strategyHolder->addStrategy($this->strategy);
        $this->assertEquals($strategies, $this->strategyHolder->getStrategies());
    }
}
