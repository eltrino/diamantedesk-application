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
namespace Eltrino\EmailProcessingBundle\Api\Impl;

use Eltrino\EmailProcessingBundle\Api\EmailProcessingService;
use Eltrino\EmailProcessingBundle\Infrastructure\Message\Zend\MessageConverter;
use Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings;
use Eltrino\EmailProcessingBundle\Model\Message\MessageProvider;
use Eltrino\EmailProcessingBundle\Model\Message\MessageProviderFactory;
use Eltrino\EmailProcessingBundle\Model\Processing\Context;
use Eltrino\EmailProcessingBundle\Model\Service\ManagerInterface;

class EmailProcessingServiceImpl implements EmailProcessingService
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var MessageProviderFactory
     */
    private $mailboxMessageProviderFactory;

    /**
     * @var MessageProviderFactory
     */
    private $rawMessageProviderFactory;

    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var MessageProvider
     */
    private $mailboxMessageProvider;

    public function __construct(ManagerInterface $manager,
                                MessageProviderFactory $mailboxMessageProviderFactory,
                                MessageProviderFactory $rawMessageProviderFactory,
                                SystemSettings $settings
    ) {
        $this->manager = $manager;
        $this->mailboxMessageProviderFactory = $mailboxMessageProviderFactory;
        $this->rawMessageProviderFactory = $rawMessageProviderFactory;
        $this->settings = $settings;
    }

    /**
     * Run Email Processing
     * @return void
     */
    public function process()
    {
        $this->manager->handle($this->getMailBoxMessageProvider());
    }

    private function getMailBoxMessageProvider()
    {
        if (is_null($this->mailboxMessageProvider)) {
            $this->mailboxMessageProvider = $this->mailboxMessageProviderFactory->create(array(
                'host' => $this->settings->getServerAddress(),
                'user' => $this->settings->getUsername(),
                'password' => $this->settings->getPassword(),
                'ssl' => $this->settings->getSslEnabled()
            ));
        }
        return $this->mailboxMessageProvider;
    }

    /**
     * Run Email Process of given message
     * @param string $input
     * @return void
     */
    public function pipe($input)
    {
        $this->manager->handle($this->rawMessageProviderFactory->create(array('raw_message' => $input)));
    }
}
