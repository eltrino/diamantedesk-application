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
namespace Diamante\EmailProcessingBundle\Infrastructure\Message;

use Diamante\EmailProcessingBundle\Infrastructure\Message\Zend\MessageConverter;
use Diamante\EmailProcessingBundle\Infrastructure\Message\Zend\RawMessageProvider;
use Diamante\EmailProcessingBundle\Model\Message\MessageProvider;
use Diamante\EmailProcessingBundle\Model\Message\MessageProviderFactory;
use Diamante\EmailProcessingBundle\Model\MessageProcessingException;

class ZendRawMessageProviderFactory implements MessageProviderFactory
{
    /**
     * @var RawMessageProvider
     */
    public $rawMessageProvider;

    public function __construct(RawMessageProvider $rawMessageProvider)
    {
        $this->rawMessageProvider = $rawMessageProvider;
    }

    /**
     * Create message provider
     * @param array $params
     * @return MessageProvider
     */
    public function create(array $params)
    {
        if (!isset($params['raw_message']) || false === is_string($params['raw_message'])) {
            throw new MessageProcessingException('Input raw message is missed or has a wrong type.');
        }

        $this->rawMessageProvider->setRawStorage($params['raw_message'], new MessageConverter());

        return $this->rawMessageProvider;
    }
}
