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

class Pop3Adapter implements Storage
{
    private $storage;

    /**
     * array(
     *  'host' => 'example.com',
     *  'user' => 'username',
     *  'password' => 'password',
     *  'ssl' => 'SSL'
     *  )
     * @param array $params
     */
    public function __construct(\Zend\Mail\Storage\Pop3 $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Retrieves unread messages from mail storage
     * @return array
     */
    public function listUnreadMessages()
    {
        $id = 0;
        $messages = new \SplFixedArray($this->storage->countMessages());
        /** @var \Zend\Mail\Storage\Message $message */
        foreach ($this->storage as $message) {
            $messages[$id] = $message;
            $id++;
        }
        return $messages;
    }
}
