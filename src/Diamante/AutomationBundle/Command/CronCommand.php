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

use Diamante\AutomationBundle\Entity\Schedule;
use JMS\JobQueueBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CronCommand
 *
 * @package Diamante\AutomationBundle\Command
 */
class CronCommand extends ContainerAwareCommand
{
    const QUEUE_NAME = 'diamante_cron';

    protected function configure()
    {
        $this
            ->setName('diamante:cron')
            ->setDescription('Diamante cron commands launcher');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // check for maintenance mode - do not run cron jobs if it is switched on
        if ($this->getContainer()->get('oro_platform.maintenance')->isOn()) {
            $output->writeln('');
            $output->writeln('<error>System is in maintenance mode, aborting</error>');

            return 255;
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $daemon = $this->getContainer()->get('oro_cron.job_daemon');
        $schedules = $em->getRepository('DiamanteAutomationBundle:Schedule')->findAll();

        // check if daemon is running
        if (!$daemon->getPid()) {
            $output->writeln('');
            $output->write('Daemon process not found, running.. ');

            if ($pid = $daemon->run()) {
                $output->writeln(sprintf('<info>OK</info> (pid: %u)', $pid));
            } else {
                $output->writeln('<error>failed</error>. Cron jobs can\'t be launched.');

                return 255;
            }
        }

        foreach ($schedules as $schedule) {
            $command = $schedule->getCommand();
            $parameters = $schedule->getParameters();
            $commandOutput = sprintf('Processing command "<info>%s</info>" ', $command);

            $output->write($commandOutput);

            $cron = \Cron\CronExpression::factory($schedule->getDefinition());

            if ($cron->isDue()) {
                $job = new Job(
                    $schedule->getCommand(),
                    $this->getFormattedParameters($parameters),
                    true,
                    self::QUEUE_NAME
                );

                $em->persist($job);

                $output->writeln('<comment>added to job queue</comment>');
            } else {
                $output->writeln('<comment>skipped</comment>');
            }
        }

        $em->flush();

        $output->writeln('');
        $output->writeln('All commands finished');

        return 0;
    }

    /**
     * @param array|null $parameters
     *
     * @return array
     */
    private function getFormattedParameters(array $parameters = null)
    {
        $formattedParameters = [];

        if (is_null($parameters)) {
            return $formattedParameters;
        }

        foreach ($parameters as $parameter => $value) {
            $formattedParameters[] = sprintf('--%s=%s', $parameter, $value);
        }

        return $formattedParameters;
    }
}
