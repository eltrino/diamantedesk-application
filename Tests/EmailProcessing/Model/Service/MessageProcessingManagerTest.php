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

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->manager = new MessageProcessingManager($this->context);
    }

    /**
     * @test
     */
    public function thatHandles()
    {
        $messages = array(new Message());
        $this->provider->expects($this->once())->method('fetchMessagesToProcess')->will($this->returnValue($messages));
        $this->context->expects($this->once())->method('execute')
            ->with($this->isInstanceOf('Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message'));
        $this->manager->handle($this->provider);
    }
}
