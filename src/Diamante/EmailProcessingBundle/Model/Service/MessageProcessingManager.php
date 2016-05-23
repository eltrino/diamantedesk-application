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
namespace Diamante\EmailProcessingBundle\Model\Service;

use Diamante\EmailProcessingBundle\Model\Mail\SystemSettings;
use Diamante\EmailProcessingBundle\Model\Message\MessageProvider;
use Diamante\EmailProcessingBundle\Model\Processing\Context;
use Diamante\EmailProcessingBundle\Model\Processing\StrategyHolder;
use Symfony\Bridge\Monolog\Logger;

class MessageProcessingManager implements ManagerInterface
{
    /**
     * @var Context
     */
    private $processingContext;

    private $strategyHolder;

    private $logger;

    /**
     * @var SystemSettings
     */
    private $settings;

    public function __construct(Context $processingContext, StrategyHolder $strategyHolder, Logger $logger, SystemSettings $settings)
    {
        $this->processingContext = $processingContext;
        $this->strategyHolder    = $strategyHolder;
        $this->logger            = $logger;
        $this->settings          = $settings;
    }

    /**
     * Handle mail process
     * @param MessageProvider $provider
     * @return void
     */
    public function handle(MessageProvider $provider)
    {
        $processedMessages = [];
        $strategies = $this->strategyHolder->getStrategies();
        foreach ($provider->fetchMessagesToProcess() as $message) {
            foreach ($strategies as $strategy) {
                $this->processingContext->setStrategy($strategy);
                try {
                    if (!$message->isFailed()) {
                        $this->processingContext->execute($message);
                    }

                    if (false === isset($processedMessages[$message->getUniqueId()])) {
                        $processedMessages[$message->getUniqueId()] = $message;
                    }
                } catch (\Exception $e) {
                    // TODO 
                    $this->logger->error(sprintf('Error processing message: %s', $e->getMessage()));
                }
            }
        }

        if ($this->settings->getDeleteProcessedMessages()) {
            $provider->deleteProcessedMessages($processedMessages);
        } else {
            $provider->markMessagesAsProcessed($processedMessages);
        }
    }
}
