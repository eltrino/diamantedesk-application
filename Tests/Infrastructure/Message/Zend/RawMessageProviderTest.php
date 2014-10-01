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
namespace Eltrino\EmailProcessingBundle\Tests\Infrastructure\Message\Zend;

use Eltrino\EmailProcessingBundle\Infrastructure\Message\Zend\RawMessageProvider;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Eltrino\EmailProcessingBundle\Infrastructure\Message\Zend\Mail\ZendMailMessage;
use Zend\Mail\AddressList;
use Zend\Mail\Header\From;
use Zend\Mail\Header\MessageId;
use Zend\Mail\Header\To;
use Zend\Mail\Headers;

class RawMessageProviderTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_RAW_MESSAGE = 'dummy_raw_message';

    /**
     * @var RawMessageProvider
     */
    private $messageProvider;

    /**
     * @var \Eltrino\EmailProcessingBundle\Infrastructure\Message\Zend\MessageConverter
     * @Mock \Eltrino\EmailProcessingBundle\Infrastructure\Message\Zend\MessageConverter
     */
    private $converter;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->messageProvider = new RawMessageProvider(self::DUMMY_RAW_MESSAGE, $this->converter);
    }

    /**
     * @test
     */
    public function thatMessagesAreFetched()
    {
        $zendMailMessage = new ZendMailMessage();
        $zendMailMessage->setHeaders($this->headers());

        $this->converter->expects($this->once())->method('fromRawMessage')
            ->with($this->equalTo(self::DUMMY_RAW_MESSAGE))
            ->will($this->returnValue($zendMailMessage));

        $messages = $this->messageProvider->fetchMessagesToProcess();

        $this->assertNotEmpty($messages);
        $this->assertContainsOnlyInstancesOf('\Eltrino\EmailProcessingBundle\Model\Message', $messages);
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
