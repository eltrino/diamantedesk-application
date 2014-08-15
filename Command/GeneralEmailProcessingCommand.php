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
namespace Eltrino\EmailProcessingBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeneralEmailProcessingCommand extends ContainerAwareCommand implements CronCommandInterface
{
    protected function configure()
    {
        $this
            ->setName('oro:cron:diamante:emailprocessing:general')
            ->setDescription('General Email Processing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Start General Email Processing");
        $this->getContainer()->get('diamante.email_processing.service')->process();
        $output->writeln("General Email Processing Done");
    }

    /**
     * Run current command every 5 minutes
     * @return string
     */
    public function getDefaultDefinition()
    {
        return '*/5 * * * *';
    }
}
