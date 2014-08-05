<?php
/**
 * Created by PhpStorm.
 * User: psw
 * Date: 8/4/14
 * Time: 6:47 PM
 */
namespace Eltrino\DiamanteDeskBundle\Tests\EmailProcessing\Infrastructure\Mail\Zend;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Infrastructure\Mail\Zend\ImapAdapter;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Zend\Mail\Storage\Message;

class ImapAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImapAdapter
     */
    private $imapAdapter;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->imapAdapter = new ImapAdapter(new ZendMailImapDummyStorage($this->messages()));
    }

    /**
     * @test
     */
    public function thatListsUnreadMessages()
    {
        $messages = $this->imapAdapter->listUnreadMessages();
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
