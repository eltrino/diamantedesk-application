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
namespace Eltrino\DiamanteDeskBundle\Tests\EmailProcessing\Model\Service;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Service\MessageProcessingManager;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class MessageProcessingManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageProcessingManager
     */
    private $manager;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProvider
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProvider
     */
    private $provider;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Context
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Context
     */
    private $context;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\StrategyHolder
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\StrategyHolder
     */
    private $strategyHolder;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Strategy
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Strategy
     */
    private $strategy;


    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->manager = new MessageProcessingManager($this->context, $this->strategyHolder);
    }

    /**
     * @test
     */
    public function thatHandles()
    {
        $messages = array(new Message('dummy_unique_id', 'DUMMY_CONTENT'));
        $strategies = array($this->strategy);

        $this->provider->expects($this->once())->method('fetchMessagesToProcess')->will($this->returnValue($messages));
        $this->strategyHolder->expects($this->once())->method('getStrategies')->will($this->returnValue($strategies));
        $this->context->expects($this->exactly(count($strategies)))->method('setStrategy')
            ->with($this->isInstanceOf('\Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Strategy'));
        $this->context->expects($this->exactly(count($messages) * count($strategies)))->method('execute')
            ->with($this->isInstanceOf('Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message'));
        $this->provider->expects($this->once())->method('markMessagesAsProcessed')
            ->with($this->logicalAnd(
                    $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY),
                    $this->countOf(count($messages)),
                    $this->callback(function($other) {
                        $result = true;
                        foreach ($other as $message) {
                            $constraint = \PHPUnit_Framework_Assert::isInstanceOf(
                                'Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message'
                            );
                            try {
                                \PHPUnit_Framework_Assert::assertThat($message, $constraint);
                            } catch (PHPUnit_Framework_ExpectationFailedException $e) {
                                $result = false;
                            }
                        }
                        return $result;
                    })
                )
            );

        $this->manager->handle($this->provider);
    }
}
