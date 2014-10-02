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
namespace Diamante\EmailProcessingBundle\Tests\Infrastructure\Message\Zend;

use Diamante\EmailProcessingBundle\Infrastructure\Message\Zend\ImapMessageProvider;
use Diamante\EmailProcessingBundle\Model\Message\MessageProvider;
use Zend\Mail\AddressList;
use Zend\Mail\Header\From;
use Zend\Mail\Header\MessageId;
use Zend\Mail\Header\To;
use Zend\Mail\Headers;
use Zend\Mail\Storage\Message;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class ImapMessageProviderTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_BATCH_SIZE_IN_BYTES = 5;

    const DUMMY_MESSAGE_ID        = 'dummy_message_id';
    const DUMMY_MESSAGE_SUBJECT   = 'dummy_message_subject';
    const DUMMY_MESSAGE_CONTENT   = 'dummy_message_content';
    const DUMMY_MESSAGE_FROM      = 'dummy_message_from';
    const DUMMY_MESSAGE_TO        = 'dummy_message_to';
    const DUMMY_MESSAGE_REFERENCE = 'dummy_message_reference';

    /**
     * @var \Zend\Mail\Storage\Imap
     * @Mock \Zend\Mail\Storage\Imap
     */
    private $zendImapStorage;

    /**
     * @var ImapMessageProvider
     */
    private $messageProvider;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->messageProvider = new ImapMessageProvider(
            new ZendImapDummyStorage($this->messages()),
            self::DUMMY_BATCH_SIZE_IN_BYTES
        );
    }

    /**
     * @test
     * @expectedException \Diamante\EmailProcessingBundle\Model\MessageProcessingException
     * @expectsExceptionMessage Dummy_Exception_Message
     */
    public function thatExceptionThrowsWhenFetchingMessages()
    {
        $this->zendImapStorage->expects($this->once())->method('getSize')
            ->will(
                $this->throwException(
                    new \Zend\Mail\Protocol\Exception\RuntimeException('Dummy_Exception_Message')
                )
            );

        $messageProvider = new ImapMessageProvider($this->zendImapStorage, self::DUMMY_BATCH_SIZE_IN_BYTES);
        $messageProvider->fetchMessagesToProcess();
    }

    /**
     * @test
     */
    public function thatMessagesAreFetched()
    {
        $messages = $this->messageProvider->fetchMessagesToProcess();
        $this->assertNotEmpty($messages);
        $this->assertContainsOnlyInstancesOf('\Diamante\EmailProcessingBundle\Model\Message', $messages);
        $this->assertCount(2, $messages);
    }

    /**
     * @test
     */
    public function thatMessagesMarkedAsProcessedAndProcessedFolderCreates()
    {
        $this->zendImapStorage->expects($this->any())->method('getNumberByUniqueId')
            ->with($this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING))
            ->will($this->returnValue(1));

        $this->zendImapStorage->expects($this->once())->method('getFolders')->will(
            $this->returnValue(
                new \Zend\Mail\Storage\Folder('/', '/', false, array(
                        new \Zend\Mail\Storage\Folder('INBOX'),
                        new \Zend\Mail\Storage\Folder('SENT'))
                )
            )
        );
        $this->zendImapStorage->expects($this->once())->method('createFolder')->with(
            $this->equalTo(ImapMessageProvider::NAME_OF_FOLDER_OF_PROCESSED_MESSAGES),
            $this->logicalAnd(
                $this->isInstanceOf('\Zend\Mail\Storage\Folder'),
                $this->attributeEqualTo('localName', 'INBOX')
            )
        );

        $this->zendImapStorage->expects($this->any())->method('move')->with($this->equalTo(1), $this->logicalAnd(
            $this->isInstanceOf('\Zend\Mail\Storage\Folder'),
            $this->attributeEqualTo('localName', 'INBOX/' . ImapMessageProvider::NAME_OF_FOLDER_OF_PROCESSED_MESSAGES)
        ));

        $messageProvider = new ImapMessageProvider($this->zendImapStorage);
        $messages = array();
        foreach ($this->messages() as $message) {
            $messages[] = new \Diamante\EmailProcessingBundle\Model\Message(
                $message['unique_id'],
                self::DUMMY_MESSAGE_ID,
                self::DUMMY_MESSAGE_SUBJECT,
                self::DUMMY_MESSAGE_CONTENT,
                self::DUMMY_MESSAGE_FROM,
                self::DUMMY_MESSAGE_TO,
                self::DUMMY_MESSAGE_REFERENCE
            );
        }
        $messageProvider->markMessagesAsProcessed($messages);
    }

    /**
     * @test
     */
    public function thatMessagesMarkedAsProcessedAndProcessedFolderAlreadyExists()
    {
        $this->zendImapStorage->expects($this->any())->method('getNumberByUniqueId')
            ->with($this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING))
            ->will($this->returnValue(1));

        $this->zendImapStorage->expects($this->once())->method('getFolders')->will(
            $this->returnValue(
                new \Zend\Mail\Storage\Folder('/', '/', false, array(
                        new \Zend\Mail\Storage\Folder('INBOX'),
                        new \Zend\Mail\Storage\Folder('SENT'),
                        new \Zend\Mail\Storage\Folder(ImapMessageProvider::NAME_OF_FOLDER_OF_PROCESSED_MESSAGES))
                )
            )
        );

        $this->zendImapStorage->expects($this->never())->method('createFolder');

        $this->zendImapStorage->expects($this->any())->method('move')->with($this->equalTo(1), $this->logicalAnd(
            $this->isInstanceOf('\Zend\Mail\Storage\Folder'),
            $this->attributeEqualTo('localName', 'INBOX/' . ImapMessageProvider::NAME_OF_FOLDER_OF_PROCESSED_MESSAGES)
        ));

        $messageProvider = new ImapMessageProvider($this->zendImapStorage);
        $messages = array();
        foreach ($this->messages() as $message) {
            $messages[] = new \Diamante\EmailProcessingBundle\Model\Message(
                $message['unique_id'],
                self::DUMMY_MESSAGE_ID,
                self::DUMMY_MESSAGE_SUBJECT,
                self::DUMMY_MESSAGE_CONTENT,
                self::DUMMY_MESSAGE_FROM,
                self::DUMMY_MESSAGE_TO,
                self::DUMMY_MESSAGE_REFERENCE
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
                'message' => new Message(array('headers' => $this->headers(), 'content' => 'DUMMY_CONTENT'))
            ),
            2 => array(
                'unique_id' => 'u2',
                'size' => 3,
                'message' => new Message(array('headers' => $this->headers(), 'content' => 'DUMMY_CONTENT'))
            ),
            3 => array(
                'unique_id' => 'u3',
                'size' => 5,
                'message' => new Message(array('headers' => $this->headers(), 'content' => 'DUMMY_CONTENT'))
            )
        );
    }

    private function headers()
    {
        $headers = new Headers();

        $messageId = new MessageId();
        $messageId->setId('testId');
        $headers->addHeader($messageId);

        $addressList = new AddressList();
        $addressList->add('test@gmail.com');

        $from = new From();
        $from->setAddressList($addressList);
        $headers->addHeader($from);

        $to = new To();
        $to->setAddressList($addressList);
        $headers->addHeader($to);

        return $headers;
    }
}
