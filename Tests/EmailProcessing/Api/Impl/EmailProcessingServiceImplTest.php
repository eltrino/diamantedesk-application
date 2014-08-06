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
namespace Eltrino\DiamanteDeskBundle\Tests\EmailProcessing\Api\Impl;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Api\EmailProcessingService;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Api\Impl\EmailProcessingServiceImpl;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class EmailProcessingServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_RAW_MESSAGE = 'DUMMY_RAW_MESSAGE';

    /**
     * @var EmailProcessingServiceImpl
     */
    private $emailProcessingService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Service\ManagerInterface
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Service\ManagerInterface
     */
    private $manager;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProviderFactory
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProviderFactory
     */
    private $messageProviderFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProvider
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProvider
     */
    private $messageProvider;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->emailProcessingService = new EmailProcessingServiceImpl(
            $this->manager,
            $this->messageProviderFactory,
            $this->messageProviderFactory
        );
    }

    /**
     * @test
     */
    public function thatEmailsProcessHandlesViaManager()
    {
        $this->messageProviderFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->messageProvider));

        $this->manager->expects($this->once())->method('handle')->with(
            $this->logicalAnd(
                $this->isInstanceOf('Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProvider')
            )
        );

        $this->emailProcessingService->process();
    }

    /**
     * @test
     */
    public function thatPipeProcessHandlesViaManager()
    {
        $this->messageProviderFactory->expects($this->once())->method('create')
            ->with($this->equalTo(array('raw_message' => self::DUMMY_RAW_MESSAGE)))
            ->will($this->returnValue($this->messageProvider));

        $this->manager->expects($this->once())->method('handle')->with(
            $this->logicalAnd(
                $this->isInstanceOf('Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProvider')
            )
        );

        $this->emailProcessingService->pipe(self::DUMMY_RAW_MESSAGE);
    }
}
