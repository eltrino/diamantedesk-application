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
namespace Eltrino\DiamanteDeskBundle\EmailProcessing\Api\Impl;

use Eltrino\DiamanteDeskBundle\EmailProcessing\Api\EmailProcessingService;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\MailStorageMessageProvider;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Message\PlainTextConverterMessageProvider;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Context;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Service\ManagerInterface;

class EmailProcessingServiceImpl implements EmailProcessingService
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Run Email Processing
     * @return void
     */
    public function process()
    {
        $provider = new MailStorageMessageProvider();
        $this->manager->handle($provider);
    }

    /**
     * Run Email Process of given message
     * @param string $input
     * @return void
     */
    public function pipe($input)
    {
        $provider = new PlainTextConverterMessageProvider();
        $this->manager->handle($provider);
    }
}
