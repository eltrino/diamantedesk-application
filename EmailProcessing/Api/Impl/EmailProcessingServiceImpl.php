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
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Processing\Context;
use Eltrino\DiamanteDeskBundle\EmailProcessing\Model\Service\MailService;

class EmailProcessingServiceImpl implements EmailProcessingService
{
    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var Context
     */
    private $processingContext;

    public function __construct(MailService $mailService, Context $processingContext)
    {
        $this->mailService = $mailService;
        $this->processingContext = $processingContext;
    }

    /**
     * Run Email Processing
     * @return void
     */
    public function process()
    {
        $messages = $this->mailService->getUnreadMessages();
        foreach ($messages as $message) {
            $this->processingContext->execute($message);
        }
    }

    /**
     * Run Email Process of given message
     * @param string $message
     * @return void
     */
    public function pipe($message)
    {
        // TODO: Implement pipe() method.
    }
}
