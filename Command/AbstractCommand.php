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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected $packageDir;

    protected $appDir;

    protected $webRoot;

    /**
     * Initializes parameters required for installation process
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = $this->getContainer()->get('filesystem');
        $kernel = $this->getContainer()->get('kernel');
        $this->packageDir = $kernel->locateResource('@DiamanteFrontBundle');
        $this->appDir = $kernel->locateResource('@DiamanteFrontBundle/Resources/front');
        $this->webRoot = $kernel->getRootDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'front';
    }

    /**
     * Copy all files related to front application into web root folder
     *
     * @param OutputInterface $output
     */
    protected function syncWebRoot(OutputInterface $output)
    {
        $output->write("Updating web root folder ...");
        $this->filesystem->mirror($this->appDir, $this->webRoot);
        $output->writeln("Done");
    }

    /**
     * @param string $command
     * @param OutputInterface $output
     */
    protected function executeProcess($command, OutputInterface $output)
    {
        $process = new Process($command, $this->packageDir);
        $process->run(function ($type, $buffer) use ($output) {
            if (Process::ERR != $type) {
                $output->write($buffer);
            } else {
                $output->write('<error>' . $buffer . '<error>');
            }
        });
    }
}
