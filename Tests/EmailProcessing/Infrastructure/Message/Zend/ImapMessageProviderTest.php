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
use Zend\Mail\Storage\Message;

class ImapMessageProviderTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_BATCH_SIZE_IN_BYTES = 5;

    /**
     * @var ImapMessageProvider
     */
    private $messageProvider;

    protected function setUp()
    {
        $this->messageProvider = new ImapMessageProvider(
            new ZendImapDummyStorage($this->messages()), self::DUMMY_BATCH_SIZE_IN_BYTES
        );
    }

    /**
     * @test
     * @expectedException \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\MessageProcessingException
     * @expectsExceptionMessage Dummy_Exception_Message
     */
    public function thatExceptionThrowsWhenFetchingMessages()
    {
        $zendImapStorage = $this->getMockBuilder('\Zend\Mail\Storage\Imap')
            ->disableOriginalConstructor()
            ->getMock();
        $zendImapStorage->expects($this->once())->method('getSize')
            ->will(
                $this->throwException(
                    new \Zend\Mail\Protocol\Exception\RuntimeException('Dummy_Exception_Message')
                )
            );
        $messageProvider = new ImapMessageProvider($zendImapStorage, self::DUMMY_BATCH_SIZE_IN_BYTES);
        $messageProvider->fetchMessagesToProcess();
    }

    /**
     * @test
     */
    public function thatMessagesAreFetched()
    {
        $messages = $this->messageProvider->fetchMessagesToProcess();
        $this->assertNotEmpty($messages);
        $this->assertContainsOnlyInstancesOf('\Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message', $messages);
        $this->assertCount(2, $messages);
    }

    /**
     * @test
     */
    public function thatMessagesMarkedAsProcessedAndProcessedFolderCreates()
    {
        $zendImapStorage = $this->getMockBuilder('\Zend\Mail\Storage\Imap')
            ->disableOriginalConstructor()
            ->getMock();
        $zendImapStorage->expects($this->any())->method('getNumberByUniqueId')
            ->with($this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING))
            ->will($this->returnValue(1));
        $zendImapStorage->expects($this->once())->method('getFolders')->will(
            $this->returnValue(
                new \Zend\Mail\Storage\Folder('/', '/', false, array(
                    new \Zend\Mail\Storage\Folder('INBOX'),
                    new \Zend\Mail\Storage\Folder('SENT'))
                )
            )
        );
        $zendImapStorage->expects($this->once())->method('createFolder')->with(
            $this->equalTo(ImapMessageProvider::NAME_OF_FOLDER_OF_PROCESSED_MESSAGES),
            $this->logicalAnd(
                $this->isInstanceOf('\Zend\Mail\Storage\Folder'),
                $this->attributeEqualTo('localName', 'INBOX')
            )
        );
        $zendImapStorage->expects($this->any())->method('move')->with($this->equalTo(1), $this->logicalAnd(
            $this->isInstanceOf('\Zend\Mail\Storage\Folder'),
            $this->attributeEqualTo('localName', 'INBOX/' . ImapMessageProvider::NAME_OF_FOLDER_OF_PROCESSED_MESSAGES)
        ));
        $messageProvider = new ImapMessageProvider($zendImapStorage);
        $messages = array();
        foreach ($this->messages() as $message) {
            $messages[] = new \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message(
                $message['unique_id'], $message['message']->getContent()
            );
        }
        $messageProvider->markMessagesAsProcessed($messages);
    }

    /**
     * @test
     */
    public function thatMessagesMarkedAsProcessedAndProcessedFolderAlreadyExists()
    {
        $zendImapStorage = $this->getMockBuilder('\Zend\Mail\Storage\Imap')
            ->disableOriginalConstructor()
            ->getMock();
        $zendImapStorage->expects($this->any())->method('getNumberByUniqueId')
            ->with($this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING))
            ->will($this->returnValue(1));
        $zendImapStorage->expects($this->once())->method('getFolders')->will(
            $this->returnValue(
                new \Zend\Mail\Storage\Folder('/', '/', false, array(
                    new \Zend\Mail\Storage\Folder('INBOX'),
                    new \Zend\Mail\Storage\Folder('SENT'),
                    new \Zend\Mail\Storage\Folder(ImapMessageProvider::NAME_OF_FOLDER_OF_PROCESSED_MESSAGES))
                )
            )
        );
        $zendImapStorage->expects($this->never())->method('createFolder');
        $zendImapStorage->expects($this->any())->method('move')->with($this->equalTo(1), $this->logicalAnd(
            $this->isInstanceOf('\Zend\Mail\Storage\Folder'),
            $this->attributeEqualTo('localName', 'INBOX/' . ImapMessageProvider::NAME_OF_FOLDER_OF_PROCESSED_MESSAGES)
        ));
        $messageProvider = new ImapMessageProvider($zendImapStorage);
        $messages = array();
        foreach ($this->messages() as $message) {
            $messages[] = new \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message(
                $message['unique_id'], $message['message']->getContent()
            );
        }
        $messageProvider->markMessagesAsProcessed($messages);
    }

    /**
     * @return array
     */
    private function messages()
    {
        return array(
            1 => array(
                'unique_id' => 'u1',
                'size' => 1,
                'message' => new Message(array('headers' => array(), 'content' => 'DUMMY_CONTENT'))
            ),
            2 => array(
                'unique_id' => 'u2',
                'size' => 3,
                'message' => new Message(array('headers' => array(), 'content' => 'DUMMY_CONTENT'))
            ),
            3 => array(
                'unique_id' => 'u3',
                'size' => 5,
                'message' => new Message(array('headers' => array(), 'content' => 'DUMMY_CONTENT'))
            )
        );
    }
}
