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

namespace Diamante\AutomationBundle\Rule\Provider;

use Diamante\AutomationBundle\Model\Target;
use Diamante\AutomationBundle\Rule\Fact\Fact;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;

class RuleProviderImpl implements RuleProvider
{
    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    protected $businessRulesRepository;
    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    protected $workflowRulesRepository;

    /**
     * @param \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository $workflowRulesRepository
     * @param \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository $businessRulesRepository
     */
    public function __construct(
        DoctrineGenericRepository $workflowRulesRepository,
        DoctrineGenericRepository $businessRulesRepository
    )
    {
        $this->workflowRulesRepository = $workflowRulesRepository;
        $this->businessRulesRepository = $businessRulesRepository;
    }

    public function getWorkflowRules(Fact $fact)
    {
        return $this->workflowRulesRepository->findBy(['parent' => null, 'active' => true, 'target' => new Target($fact->getTargetType())]);
    }

    public function getBusinessRules(Fact $fact)
    {
        return $this->businessRulesRepository->findBy(['parent' => null, 'active' => true, 'target' => new Target($fact->getTargetType())]);
    }
}