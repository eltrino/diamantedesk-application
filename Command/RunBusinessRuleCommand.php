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

namespace Diamante\AutomationBundle\Command;


use Diamante\AutomationBundle\Entity\BusinessRule;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunBusinessRuleCommand extends ContainerAwareCommand
{
    /**
     * @var BusinessRule
     */
    protected $rule;

    /**
     *
     */
    protected function configure()
    {
        $this->setName("diamante:automation:business:run")
            ->addOption("rule-id", "id", InputOption::VALUE_REQUIRED, "Business rule id")
            ->addOption("dry-run", "d", InputOption::VALUE_OPTIONAL, "Do not execute actions configured in rule");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $id = (int)$input->getOption('rule-id');
        $this->rule = $this->getContainer()
            ->get('doctrine')
            ->getRepository("DiamanteAutomationBundle:BusinessRule")
            ->get($id);

        if (empty($this->rule)) {
            throw new \InvalidArgumentException(sprintf("No rule with id %d found, aborting", $id));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $engine = $this->getContainer()->get('diamante_automation.engine');
        $dryRun = $input->hasOption("dry-run") ? true : false;

        $output->writeln(sprintf("<info>Started processing rule: %s</info>", $this->rule->getName()));

        if ($dryRun) {
            $output->writeln("<info>Dry Run option is enabled, none of the actions would be run</info>");
        }

        $result = $engine->processRule($this->rule, $dryRun);

        $output->writeln(sprintf("<info>Processing finished. %d entities processed.</info>", $result));

        return 0;
    }
}