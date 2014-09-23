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
namespace Diamante\FrontBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('diamante:front:install')
            ->setDescription('Install Diamante Front')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will ignore any checks'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->filesystem->exists($this->webRoot) && !$input->getOption('force')) {
            throw new \RuntimeException(sprintf('Web root folder "%s" exists. Use --force option to proceed with this operation.', realpath($this->webRoot)));
        }
        $output->write(sprintf('Making web root folder "%s"...', realpath($this->webRoot)));
        $this->filesystem->mkdir($this->webRoot);
        $output->writeln("Done");

        $output->write("Installing assets dependencies ...");
        $this->executeProcess('bower install', $output);
        $output->writeln("Done");

        // @todo configure application

        $this->syncWebRoot($output);
    }
}
