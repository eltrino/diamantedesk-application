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
namespace Eltrino\DiamanteDeskBundle\Tests\EmailProcessing\Infrastructure\Mail\Zend;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Infrastructure\Mail\Zend\Pop3Adapter;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class Pop3AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pop3Adapter
     */
    private $pop3Adapter;

    /**
     * @var \Zend\Mail\Storage\Pop3
     * @Mock \Zend\Mail\Storage\Pop3
     */
    private $pop3Storage;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->pop3Adapter = new Pop3Adapter(new ZendMailPop3DummyStorage(array()));
    }

    /**
     * @test
     */
    public function thatListsUnreadMessages()
    {
        $messages = $this->pop3Adapter->listUnreadMessages();
        $this->assertNotEmpty($messages);
        $this->assertContainsOnlyInstancesOf('\Zend\Mail\Storage\Message', $messages);
    }
}
