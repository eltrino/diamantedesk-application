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
namespace Diamante\EmailProcessingBundle\Tests\Model\Service;

use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\EmailProcessingBundle\Model\Service\MessageProcessingManager;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class MessageProcessingManagerTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_MESSAGE_UNIQUE_ID = 'dummy_message_unique_id';
    const DUMMY_MESSAGE_ID        = 'dummy_message_id';
    const DUMMY_MESSAGE_SUBJECT   = 'dummy_message_subject';
    const DUMMY_MESSAGE_CONTENT   = 'dummy_message_content';
    const DUMMY_MESSAGE_FROM      = 'dummy_message_from';
    const DUMMY_MESSAGE_TO        = 'dummy_message_to';
    const DUMMY_MESSAGE_REFERENCE = 'dummy_message_reference';

    /**
     * @var MessageProcessingManager
     */
    private $manager;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Message\MessageProvider
     * @Mock \Diamante\EmailProcessingBundle\Model\Message\MessageProvider
     */
    private $provider;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Processing\Context
     * @Mock \Diamante\EmailProcessingBundle\Model\Processing\Context
     */
    private $context;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Processing\StrategyHolder
     * @Mock \Diamante\EmailProcessingBundle\Model\Processing\StrategyHolder
     */
    private $strategyHolder;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Processing\Strategy
     * @Mock \Diamante\EmailProcessingBundle\Model\Processing\Strategy
     */
    private $strategy;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     * @Mock Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     * @Mock \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     */
    private $settings;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->manager = new MessageProcessingManager($this->context, $this->strategyHolder, $this->logger, $this->settings);
    }

    /**
     * @test
     */
    public function thatHandlesAndMarksMessages()
    {
        $messages = $this->getMessages();
        $strategies = array($this->strategy);

        $this->settings->expects($this->any())->method('getDeleteProcessedMessages')->will($this->returnValue(false));
        $this->provider->expects($this->once())->method('fetchMessagesToProcess')->will($this->returnValue($messages));
        $this->strategyHolder->expects($this->once())->method('getStrategies')->will($this->returnValue($strategies));
        $this->context->expects($this->exactly(count($strategies)))->method('setStrategy')
            ->with($this->isInstanceOf('\Diamante\EmailProcessingBundle\Model\Processing\Strategy'));
        $this->context->expects($this->exactly(count($messages) * count($strategies)))->method('execute')
            ->with($this->isInstanceOf('Diamante\EmailProcessingBundle\Model\Message'));
        $this->provider->expects($this->once())->method('markMessagesAsProcessed')
            ->with($this->logicalAnd(
                    $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY),
                    $this->countOf(count($messages)),
                    $this->callback(function($other) {
                        $result = true;
                        foreach ($other as $message) {
                            $constraint = \PHPUnit_Framework_Assert::isInstanceOf(
                                'Diamante\EmailProcessingBundle\Model\Message'
                            );
                            try {
                                \PHPUnit_Framework_Assert::assertThat($message, $constraint);
                            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                                $result = false;
                            }
                        }
                        return $result;
                    })
                )
            );

        $this->manager->handle($this->provider);
    }

    /**
     * @test
     */
    public function thatHandlesAndDeletesMessages()
    {
        $messages = $this->getMessages();
        $strategies = array($this->strategy);

        $this->settings->expects($this->any())->method('getDeleteProcessedMessages')->will($this->returnValue(true));
        $this->provider->expects($this->once())->method('fetchMessagesToProcess')->will($this->returnValue($messages));
        $this->strategyHolder->expects($this->once())->method('getStrategies')->will($this->returnValue($strategies));
        $this->context->expects($this->exactly(count($strategies)))->method('setStrategy')
            ->with($this->isInstanceOf('\Diamante\EmailProcessingBundle\Model\Processing\Strategy'));
        $this->context->expects($this->exactly(count($messages) * count($strategies)))->method('execute')
            ->with($this->isInstanceOf('Diamante\EmailProcessingBundle\Model\Message'));
        $this->provider->expects($this->once())->method('deleteProcessedMessages');

        $this->manager->handle($this->provider);
    }

    protected  function getMessages()
    {
        return [new Message(
            self::DUMMY_MESSAGE_UNIQUE_ID,
            self::DUMMY_MESSAGE_ID,
            self::DUMMY_MESSAGE_SUBJECT,
            self::DUMMY_MESSAGE_CONTENT,
            $this->getDummyFrom(),
            self::DUMMY_MESSAGE_TO,
            self::DUMMY_MESSAGE_REFERENCE)
        ];
    }

    protected function getDummyFrom()
    {
        return new Message\MessageSender(self::DUMMY_MESSAGE_FROM, 'Dummy Name');
    }
}
