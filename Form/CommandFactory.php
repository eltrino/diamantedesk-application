<?php
namespace Diamante\AutomationBundle\Form;

use Diamante\AutomationBundle\Api\Command\WorkflowRuleCommand;
use Diamante\AutomationBundle\Api\Command\BusinessRuleCommand;
use Diamante\AutomationBundle\Entity\WorkflowRule;
use JMS\Serializer\SerializerBuilder;
use Diamante\AutomationBundle\Model\Rule;

class CommandFactory
{
    const BUSINESS = 'business';
    const WORKFLOW = 'workflow';

    public function createRuleCommand($mode)
    {
        $command = $this->getRuleCommand($mode);
        $command->mode = $mode;

        return $command;
    }

    public function getRuleCommand($mode)
    {
        if (self::BUSINESS == $mode) {
            return new BusinessRuleCommand();
        }

        return new WorkflowRuleCommand();
    }
}
