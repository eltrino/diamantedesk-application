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
namespace Diamante\EmbeddedFormBundle\Command;

use Diamante\DeskBundle\Command\AbstractCommand;
use Oro\Bundle\MigrationBundle\Entity\DataMigration;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends AbstractCommand
{
    const EMBEDDED_FORM_BUNDLE_NAME = 'DiamanteEmbeddedFormBundle';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:embeddedform:install')
            ->setDescription('Install DiamanteDesk Embedded Forms');
    }

    /**
     * Executes installation
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->resetMigrationData();

        $commandName = 'oro:migration:load';
        $command = $this->getApplication()->find($commandName);

        $arguments = [
            'command'   => $commandName,
            '--bundles' => [static::EMBEDDED_FORM_BUNDLE_NAME],
            '--force'   => true,
        ];

        $input = new ArrayInput($arguments);
        try {
            $command->run($input, $output);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return 255;
        }

        $output->writeln("Installed!" . "\n");
        return 0;
    }

    /**
     * Remove information from DataMigrationBundle
     */
    private function resetMigrationData()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $record = $em->getRepository('OroMigrationBundle:DataMigration')->findOneByBundle(static::EMBEDDED_FORM_BUNDLE_NAME);
        if ($record) {
            $em->remove($record);
            $em->flush();
        }
    }
}