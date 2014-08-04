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

use Zend\Mail\Storage\Exception;
use Zend\Mail\Storage\Message;

class ZendMailPop3DummyStorage extends \Zend\Mail\Storage\Pop3
{
    private $messages;

    /**
     * Create instance with parameters
     *
     * @param  array $params mail reader specific parameters
     * @throws Exception\ExceptionInterface
     */
    public function __construct($params)
    {
        $this->messages = array();
        $this->initialize();
    }

    private function initialize()
    {
        $this->messages = array(
            1 => new Message(array()),
            2 => new Message(array())
        );
    }

    /**
     * Count messages messages in current box/folder
     *
     * @return int number of messages
     * @throws Exception\ExceptionInterface
     */
    public function countMessages()
    {
        return count($this->messages);
    }

    /**
     * Get a list of messages with number and size
     *
     * @param  int $id number of message
     * @return int|array size of given message of list with all messages as array(num => size)
     */
    public function getSize($id = 0)
    {
        // TODO: Implement getSize() method.
    }

    /**
     * Get a message with headers and body
     *
     * @param  $id int number of message
     * @return Message
     */
    public function getMessage($id)
    {
        if (!isset($this->messages[$id])) {
            return null;
        }
        return $this->messages[$id];
    }

    /**
     * Get raw header of message or part
     *
     * @param  int $id number of message
     * @param  null|array|string $part path to part or null for message header
     * @param  int $topLines include this many lines with header (after an empty line)
     * @return string raw header
     */
    public function getRawHeader($id, $part = null, $topLines = 0)
    {
        // TODO: Implement getRawHeader() method.
    }

    /**
     * Get raw content of message or part
     *
     * @param  int $id number of message
     * @param  null|array|string $part path to part or null for message content
     * @return string raw content
     */
    public function getRawContent($id, $part = null)
    {
        // TODO: Implement getRawContent() method.
    }

    /**
     * Close resource for mail lib. If you need to control, when the resource
     * is closed. Otherwise the destructor would call this.
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * Keep the resource alive.
     */
    public function noop()
    {
        // TODO: Implement noop() method.
    }

    /**
     * delete a message from current box/folder
     *
     * @param $id
     */
    public function removeMessage($id)
    {
        // TODO: Implement removeMessage() method.
    }

    /**
     * get unique id for one or all messages
     *
     * if storage does not support unique ids it's the same as the message number
     *
     * @param int|null $id message number
     * @return array|string message number for given message or all messages as array
     * @throws Exception\ExceptionInterface
     */
    public function getUniqueId($id = null)
    {
        // TODO: Implement getUniqueId() method.
    }

    /**
     * get a message number from a unique id
     *
     * I.e. if you have a webmailer that supports deleting messages you should use unique ids
     * as parameter and use this method to translate it to message number right before calling removeMessage()
     *
     * @param string $id unique id
     * @return int message number
     * @throws Exception\ExceptionInterface
     */
    public function getNumberByUniqueId($id)
    {
        // TODO: Implement getNumberByUniqueId() method.
    }
}
