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
namespace Diamante\DeskBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:desk:update')
            ->setDescription('Update DiamanteDesk');
    }

    /**
     * Executes update process
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->write("Clearing cache..." . "\n");
            $this->runExistingCommand('cache:clear', $output);
            $output->writeln("Done" . "\n");

            $output->write("Updating DB schema..." . "\n");
            $this->updateDbSchema();
            $output->writeln("Done" . "\n");

            $this->updateEntityConfig($output);

            $output->write("Updating navigation..." . "\n");
            $this->updateNavigation($output);
            $output->writeln("Done" . "\n");
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return;
        }

        $output->writeln("Updated!" . "\n");
    }
}
