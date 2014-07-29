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
    /**
     * @var EmailProcessingService
     */
    private $emailProcessingService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Service\MailService
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Service\MailService
     */
    private $mailService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Context
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Context
     */
    private $processingContext;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->emailProcessingService = new EmailProcessingServiceImpl($this->mailService, $this->processingContext);
    }

    /**
     * @test
     */
    public function thatEmailsProcesses()
    {
        $message = new Message();
        /** @var Message[] $messages */
        $messages = array($message);
        $this->mailService->expects($this->once())->method('getUnreadMessages')->will($this->returnValue($messages));
        $this->processingContext->expects($this->exactly(count($messages)))->method('execute')
            ->with($this->isInstanceOf('\Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message'));
        $this->emailProcessingService->process();
    }
}
