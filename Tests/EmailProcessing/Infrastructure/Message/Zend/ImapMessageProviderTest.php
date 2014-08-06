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
namespace Eltrino\DiamanteDeskBundle\Tests\EmailProcessing\Infrastructure\Message\Zend;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Infrastructure\Message\Zend\ImapMessageProvider;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MessageProvider;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Zend\Mail\Storage\Message;

class ImapMessageProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImapMessageProvider
     */
    private $messageProvider;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->messageProvider = new ImapMessageProvider(new ZendImapDummyStorage($this->messages()));
    }

    /**
     * @test
     */
    public function thatMessagesAreFetched()
    {
        $messages = $this->messageProvider->fetchMessagesToProcess();
        $this->assertNotEmpty($messages);
        $this->assertContainsOnlyInstancesOf('\Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message', $messages);
    }

    /**
     * @return array
     */
    private function messages()
    {
        return array(
            1 => new Message(array('headers' => array(), 'content' => 'DUMMY_CONTENT')),
            2 => new Message(array('headers' => array(), 'content' => 'DUMMY_CONTENT'))
        );
    }
}
