<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Automation;


use Diamante\AutomationBundle\Rule\Action\ActionInterface;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\AutomationBundle\Rule\Fact\AbstractFact;
use Symfony\Bridge\Monolog\Logger;

class Scheduler
{
    protected $logger;

    /**
     * @var ActionInterface[]
     */
    protected $queue;

    protected $hasErrors = false;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param AbstractFact $fact
     */
    public function run(AbstractFact $fact)
    {
        // first move to branch and only then notify by email
        asort($this->queue);

        foreach ($this->queue as $action) {
            try {
                $action->getContext()->setFact($fact);
                $action->execute();

                if ($action->getContext()->hasErrors()) {
                    $this->hasErrors = true;
                    foreach ($action->getContext()->getErrors() as $error) {
                        $this->logger->error($error);
                    }
                    $action->getContext()->setExecutionResult(ExecutionContext::EXECUTION_FAILED);
                    return;
                }

                $action->getContext()->setExecutionResult(ExecutionContext::EXECUTION_SUCCESS);

            } catch (\Exception $e) {
                $this->hasErrors = true;
                $action->getContext()->setExecutionResult(ExecutionContext::EXECUTION_FAILED);
                $this->logger->error($e->getMessage());
            }
        }
    }

    public function isEmpty()
    {
        return empty($this->queue);
    }

    public function reset()
    {
        $this->queue = [];
        $this->hasErrors = false;
    }

    public function addAction(ActionInterface $action)
    {
        $this->queue[spl_object_hash($action)] = $action;
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->hasErrors;
    }
}