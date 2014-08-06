<?php
/**
 * Created by PhpStorm.
 * User: psw
 * Date: 8/1/14
 * Time: 5:42 PM
 */

namespace Eltrino\DiamanteDeskBundle\Tests\EmailProcessing\Model\Message;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MailStorageMessageProvider;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class MailStorageMessageProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailStorageMessageProvider
     */
    private $provider;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Mail\Factory
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Mail\Factory
     */
    private $storageFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Mail\Storage
     * @Mock \Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Mail\Storage
     */
    private $storage;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->provider = new MailStorageMessageProvider($this->storageFactory);
    }

    /**
     * @test
     */
    public function thatStorageFactoryCreatesStorageOnlyOnce()
    {
        $this->storageFactory->expects($this->once())->method('create')->will($this->returnValue($this->storage));
        $this->provider->fetchMessagesToProcess();
        $this->provider->fetchMessagesToProcess();
    }

    /**
     * @test
     */
    public function thatMessagesAreFetched()
    {
        $messages = array(new Message());

        $this->storageFactory->expects($this->once())->method('create')->will($this->returnValue($this->storage));
        $this->storage->expects($this->once())->method('listUnreadMessages')->will($this->returnValue($messages));

        $messages = $this->provider->fetchMessagesToProcess();

        $this->assertNotNull($messages);
        $this->assertInternalType('array', $messages);
        $this->assertNotEmpty($messages);
    }
}
