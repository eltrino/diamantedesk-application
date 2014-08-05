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
namespace Eltrino\DiamanteDeskBundle\EmailProcessing\Infrastructure\Mail\Zend;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Mail\Storage;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message;

class ImapAdapter implements Storage
{
    /**
     * @var \Zend\Mail\Storage\Imap
     */
    private $zendImapStorage;

    public function __construct(\Zend\Mail\Storage\Imap $zendImapStorage)
    {
        $this->zendImapStorage = $zendImapStorage;
    }

    /**
     * Retrieves unread messages from mail storage
     * @return array of Message
     */
    public function listUnreadMessages()
    {
        $messages = array();
        foreach ($this->zendImapStorage as $messageId => $message) {
            /** @var \Zend\Mail\Storage\Message $message */
            if ($message->hasFlag(\Zend\Mail\Storage::FLAG_SEEN)) {
                continue;
            }
            $messages[] = new Message($this->zendImapStorage->getUniqueId($messageId), $message->getContent());
        }

        return $messages;
    }
}
