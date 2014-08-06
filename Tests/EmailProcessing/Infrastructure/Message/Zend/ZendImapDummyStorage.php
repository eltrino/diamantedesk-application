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

class ZendImapDummyStorage extends \Zend\Mail\Storage\Imap
{
    private $messages;

    /**
     * Create instance with given array of messages
     *
     * @param  array $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @see \Zend\Mail\Storage\Imap::countMessages()
     */
    public function countMessages()
    {
        return count($this->messages);
    }

    /**
     * @see \Zend\Mail\Storage\Imap::getSize()
     */
    public function getSize($id = 0)
    {
    }

    /**
     * @see \Zend\Mail\Storage\Imap::getMessage()
     */
    public function getMessage($id)
    {
        if (!isset($this->messages[$id])) {
            return null;
        }
        return $this->messages[$id];
    }

    /**
     * @see \Zend\Mail\Storage\Imap::getRawHeader()
     */
    public function getRawHeader($id, $part = null, $topLines = 0)
    {
    }

    /**
     * @see \Zend\Mail\Storage\Imap::getRawContent()
     */
    public function getRawContent($id, $part = null)
    {
    }

    /**
     * @see \Zend\Mail\Storage\Imap::close()
     */
    public function close()
    {
    }

    /**
     * @see \Zend\Mail\Storage\Imap::noop()
     */
    public function noop()
    {
    }

    /**
     * @see \Zend\Mail\Storage\Imap::removeMessage()
     */
    public function removeMessage($id)
    {
    }

    /**
     * @see \Zend\Mail\Storage\Imap::getUniqueId()
     */
    public function getUniqueId($id = null)
    {
    }

    /**
     * @see \Zend\Mail\Storage\Imap::getNumberByUniqueId()
     */
    public function getNumberByUniqueId($id)
    {
    }
}
