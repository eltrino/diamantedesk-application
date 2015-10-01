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
namespace Diamante\EmailProcessingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PipeEmailProcessingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('diamante:emailprocessing:pipe')
            ->setDescription('Pipe Email Processing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Start Pipe Email Processing");
        $message = '';
        $in = fopen('php://stdin', 'r');
        while (!feof($in)) {
            $message .= fread($in, 1024);
        }
        fclose($in);
        $this->getContainer()->get('diamante.email_processing.service')->pipe($message);
        $output->writeln("Pipe Email Processing Done");
    }
}
