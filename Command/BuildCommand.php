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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class BuildCommand extends ContainerAwareCommand
{
    const RETURN_CODE_NO_TOOLS = 1;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $bundleDir;

    /**
     * Initializes parameters required for installation process
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = $this->getContainer()->get('filesystem');
        $kernel = $this->getContainer()->get('kernel');
        $this->bundleDir = $kernel->locateResource('@DiamanteFrontBundle');
    }

    protected function configure()
    {
        $this
            ->setName('diamante:front:build')
            ->setDescription('Build DiamanteDesk Front')
            ->addOption(
                'with-assets-dependencies',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will install/update assets dependencies for this bundle.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->isCommandSuccess('grunt --version')
            || !$this->isCommandSuccess('bower --version')
        ) {
            $output->writeln('<error>For full functionality of this bundle, you should install grunt-cli, bower globally using npm.</erroe>');
            return self::RETURN_CODE_NO_TOOLS;
        }

        if ($input->getOption('with-assets-dependencies')) {
            $output->write("Installing assets dependencies ...");
            $this->executeProcess('bower install', $output);
            $output->write("Updating assets dependencies ...");
            $this->executeProcess('bower update', $output);
        }

        $assetsDir = $this->bundleDir . DIRECTORY_SEPARATOR . 'Resources/assets';
        $publicDir = $this->bundleDir . DIRECTORY_SEPARATOR . 'Resources/public';

        $output->write("Building application ...");
        $this->executeProcess([
            sprintf('grunt sync --assets-dir=%s --public-dir=%s', $assetsDir, $publicDir),
            sprintf('grunt less --assets-dir=%s --public-dir=%s', $assetsDir, $publicDir)
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

        $process = new Process($command, $this->bundleDir);

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
