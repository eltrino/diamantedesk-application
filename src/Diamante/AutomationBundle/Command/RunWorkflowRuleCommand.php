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


use Diamante\AutomationBundle\Automation\Engine;
use Diamante\AutomationBundle\Entity\PersistentProcessingContext;
use Diamante\AutomationBundle\Infrastructure\Shared\TargetMapper;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunWorkflowRuleCommand extends ContainerAwareCommand
{
    /**
     * @var
     */
    protected $targetType;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $changeset;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PersistentProcessingContext
     */
    protected $context;

    /**
     *
     */
    protected function configure()
    {
        $this->setName('diamante:automation:workflow:run')
            ->addOption("context-id", "c", InputOption::VALUE_REQUIRED, "Processing Context")
            ->addOption("dry-run", 'd', InputOption::VALUE_OPTIONAL, "Do not execute configured actions")
            ->setDescription("Run single entity against the set of applicable rules");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();

        $processingContextId = $input->getOption("context-id");
        /** @var PersistentProcessingContext $processingContext */
        $processingContext   = $this->em
            ->getRepository('DiamanteAutomationBundle:PersistentProcessingContext')
            ->findOneBy(["id" => $processingContextId, "state" => PersistentProcessingContext::STATE_NEW]);

        if (empty($processingContext)) {
            throw new \RuntimeException("Invalid processing context provided");
        }

        $this->targetType = $this->getContainer()->get('diamante_automation.config.provider')->getTargetByClass(
            $processingContext->getTargetEntityClass()
        );
        $this->action = $processingContext->getAction();
        $this->changeset = $processingContext->getTargetEntityChangeset();

        $processingContext->lock();
        $this->em->persist($processingContext);
        $this->em->flush();
        $this->context = $processingContext;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->hasParameterOption('--dry-run');

        $engine = $this->getContainer()->get('diamante_automation.engine');
        $fact = $engine->createFact($this->targetType, $this->action, $this->changeset);

        try {
            $output->writeln("<info>Started rules processing</info>");

            if ($dryRun) {
                $output->writeln("<info>Dry run option is enabled, no configured actions will be run.</info>");
            }

            $engine->process($fact, $dryRun);
            $output->writeln("<info>Rule processing finished successfully.</info>");

            $this->context->release();

        } catch (\Exception $e) {
            $this->getContainer()->get('monolog.logger.diamante')->error($e->getMessage());
            $this->context->markIncomplete();
            return 255;
        }

        $this->em->persist($this->context);
        $this->em->flush();

        return 0;
    }
}