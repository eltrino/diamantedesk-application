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
            ->setDescription('Install DiamanteDesk Front')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will ignore any checks'
            )
            ->addOption(
                'with-assets',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will install/update assets. Use this option only if assets were not installed automatically or it is required to update them.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->isCommandSuccess('grunt --version')
            || !$this->isCommandSuccess('bower --version')
        ) {
            $output->writeln('<error>For full functionality of this software, you should install grunt-cli, bower globally using npm.</erroe>');
            return self::RETURN_CODE_NO_TOOLS;
        }

        if ($this->filesystem->exists($this->webRoot) && !$input->getOption('force')) {
            $output->writeln(sprintf(
                '<error>Web root folder "%s" exists. Please run the operation with --force to execute.</error>',
                $this->webRoot
            ));
            return self::RETURN_CODE_WEB_ROOT_EXISTS;
        }

        $output->write(sprintf('Making web root folder "%s"...', $this->webRoot));
        $this->filesystem->mkdir($this->webRoot);
        $output->writeln("Done");

        $output->write("Installing assets dependencies ...");
        $this->executeProcess('bower install', $output);

        $this->syncWebRoot($output);
    }
}
