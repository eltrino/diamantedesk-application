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
use Symfony\Component\Filesystem\Filesystem;

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
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('with-assets')) {
            $output->write("Updating assets dependencies ...");
            $this->executeProcess('bower install', $output);
            $output->writeln("Done");
        }

        // @todo configure application

        $this->syncWebRoot($output);
    }
}
