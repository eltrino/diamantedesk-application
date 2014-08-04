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
namespace Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Mail\Storage;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Mail\StorageFactory;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\MessageProcessingException;

class MailStorageMessageProvider implements MessageProvider
{
    /**
     * @var StorageFactory
     */
    private $storageFactory;

    /**
     * @var Storage
     */
    private $storage;

    public function __construct(StorageFactory $storageFactory)
    {
        $this->storageFactory = $storageFactory;
    }

    protected function initialize()
    {
        if (is_null($this->storage)) {
            $this->storage = $this->storageFactory->create();
        }
    }

    /**
     * Fetch messages that should be processed
     * @return array|Message[]
     * @throws MessageProcessingException
     */
    public function fetchMessagesToProcess()
    {
        $this->initialize();
        $messages = $this->storage->listUnreadMessages();
        return $messages;
    }
}
