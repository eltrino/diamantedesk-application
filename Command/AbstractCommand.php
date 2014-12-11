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
    const RETURN_CODE_NO_TOOLS = 1;
    const RETURN_CODE_WEB_ROOT_EXISTS = 2;
    const RETURN_CODE_NO_WEB_ROOT = 3;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected $packageDir;

    protected $appDir;

    protected $webRoot;

    /**
     * Initializes parameters required for installation process
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = $this->getContainer()->get('filesystem');
        $kernel = $this->getContainer()->get('kernel');
        $this->packageDir = $kernel->locateResource('@DiamanteFrontBundle');
        $this->appDir = $kernel->locateResource('@DiamanteFrontBundle/Resources/front');
        $this->webRoot = dirname($kernel->getRootDir()) . DIRECTORY_SEPARATOR . 'front';
    }

    /**
     * Copy all files related to front application into web root folder
     *
     * @param OutputInterface $output
     */
    protected function syncWebRoot(OutputInterface $output)
    {
        $output->write("Updating web root folder ...");
        $this->executeProcess([
            sprintf('grunt less --src=%s --webRoot=%s', $this->appDir, $this->webRoot),
            sprintf('grunt sync --src=%s --webRoot=%s', $this->appDir, $this->webRoot)
        ], $output);
    }

    /**
     * Executes one or more commands
     *
     * If array passed, those commands will be executed using &&
     *
     * @param string|array $command
     * @param OutputInterface $output
     * @return int
     */
    protected function executeProcess($command, OutputInterface $output)
    {
        if (is_array($command)) {
            $command = implode(' && ', $command);
        }

        // Make new line if there will be command output
        if ($output->getVerbosity() > 1) {
            $output->writeln("");
        }

        $process = new Process($command, $this->packageDir);

        $result = $process->run(function ($type, $buffer) use ($output) {
            if (Process::ERR != $type) {
                if ($output->getVerbosity() > 1) {
                    $output->write($buffer);
                }
            } else {
                if ($output->getVerbosity() > 1) {
                    $output->write('<error>' . $buffer . '<error>');
                }
            }
        });

        if ($result) {
            $output->writeln("<error>Failed</error>");
        } else {
            $output->writeln("Done");
        }

        return $result;
    }

    /**
     * Check that system command executes successfully
     *
     * @param $command
     * @return boolean
     */
    protected function isCommandSuccess($command)
    {
        $process = new Process($command);
        if ($process->run() > 0) {
            return false;
        }

        return true;
    }
}
