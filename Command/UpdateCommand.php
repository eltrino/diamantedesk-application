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

class UpdateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('diamante:front:update')
            ->setDescription('Update Diamante Front')
            ->addOption(
                'with-assets',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will update assets before updating application'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->filesystem->exists($this->webRoot)) {
            $output->writeln(sprintf(
                '<error>Web root folder "%s" does not exists. Please run diamante:front:install in the first place.</error>',
                $this->webRoot
            ));
            return self::RETURN_CODE_WEB_ROOT_EXISTS;
        }

        if ($input->getOption('with-assets')) {
            $output->write("Updating assets dependencies ...");
            $this->executeProcess('bowe1r install', $output);
        }

        $this->syncWebRoot($output);
    }
}
