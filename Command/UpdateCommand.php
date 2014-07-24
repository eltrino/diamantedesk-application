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
namespace Eltrino\DiamanteDeskBundle\Command;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class UpdateCommand extends BaseCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:update')
            ->setDescription('Update Diamante Desk');
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
            $output->write('Updating DB schema...');
            $this->updateDbSchema();
            $output->writeln('Done');

            $output->write('Updating navigation...');
            $this->updateNavigation($output);
            $output->writeln('Done');

            $output->write('Installing assets...');
            $this->assetsInstall($output);
            $this->asseticDump($output, array(
                '--no-debug' => true,
            ));
            $output->write('Done');

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return;
        }

        $output->writeln('Updated!');
    }
}
