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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpConfigCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('diamante:automation:config:dump');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Dumping automation config</info>");
        $configProvider = $this->getContainer()->get('diamante_automation.config.provider');

        $config = $configProvider->prepareConfigDump($this->getContainer()->get('translator.default'));

        $renderedConfig = $this->getContainer()->get('twig')->render('DiamanteAutomationBundle::automation.js.twig', ['config' => $config]);

        $fileName = sprintf("%s/../web/js/automation.json", $this->getContainer()->getParameter('kernel.root_dir'));

        file_put_contents($fileName, $renderedConfig);

        $output->writeln("<info>Automation config dump complete successfully.</info>");
        $output->writeln(sprintf("<info>Config dumped to %s</info>", realpath($fileName)));
    }
}