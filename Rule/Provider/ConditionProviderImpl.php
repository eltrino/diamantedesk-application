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

class ConditionProviderImpl implements ConditionProvider
{
    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    protected $businessConditionsRepository;
    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    protected $workflowConditionsRepository;


    public function __construct(
        DoctrineGenericRepository $workflowConditionsRepository,
        DoctrineGenericRepository $businessConditionsRepository
    )
    {
        $this->workflowConditionsRepository = $workflowConditionsRepository;
        $this->businessConditionsRepository = $businessConditionsRepository;
    }

    public function getWorkflowConditions(Fact $fact)
    {
        return $this->workflowConditionsRepository->findBy(['parent' => null, 'active' => true, 'target' => new Target($fact->getTargetType())]);
    }

    public function getBusinessConditions(Fact $fact)
    {
        return $this->businessConditionsRepository->findBy(['parent' => null, 'active' => true, 'target' => new Target($fact->getTargetType())]);
    }
}