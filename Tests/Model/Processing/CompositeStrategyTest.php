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
namespace Eltrino\EmailProcessingBundle\Tests\Model\Processing;

use Eltrino\EmailProcessingBundle\Model\Message;
use Eltrino\EmailProcessingBundle\Model\Processing\CompositeStrategy;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class CompositeStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeStrategy
     */
    private $compositeStrategy;

    /**
     * @var \Eltrino\EmailProcessingBundle\Model\Processing\Strategy
     * @Mock \Eltrino\EmailProcessingBundle\Model\Processing\Strategy
     */
    private $strategy;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->compositeStrategy = new CompositeStrategy();
    }

    public function testProcess()
    {
        $message = new Message(uniqid(), 'DUMMY_MESSAGE_ID', 'DUMMY_SUBJECT', 'DUMMY_CONTENT', 'DUMMY_FROM',
            'DUMMY_TO', 'DUMMY_REFERENCE');

        $this->strategy
            ->expects($this->once())
            ->method('process');

        $this->compositeStrategy->addStrategy($this->strategy);
        $this->compositeStrategy->process($message);
    }
}
