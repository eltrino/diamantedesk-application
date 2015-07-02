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

use Diamante\FrontBundle\Utility\DependencyLocator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
        $dependencies = ['grunt', 'bower'];
        $resolved     = $this->locateDependencies($dependencies);

        $grunt = array_key_exists('grunt', $resolved) ? $resolved['grunt'] : null;
        $bower = array_key_exists('bower', $resolved) ? $resolved['bower'] : null;

        if (is_null($grunt) || is_null($bower)
        ) {
            $output->writeln('<error>For full functionality of this bundle, you should install grunt-cli, bower globally using npm.</error>');
            return self::RETURN_CODE_NO_TOOLS;
        }

        if ($input->getParameterOption('--with-assets-dependencies')) {
            $output->write("Installing assets dependencies ...");
            $this->executeProcess(sprintf('%s install', $bower), $output);
            $output->write("Updating assets dependencies ...");
            $this->executeProcess(sprintf('%s update', $bower), $output);
        }

        $assetsDir = $this->bundleDir . 'Resources/assets';
        $publicDir = $this->bundleDir . 'Resources/public';

        $output->write("Building application ...");
        $this->executeProcess([
            sprintf('%s sync --assets-dir=%s --public-dir=%s', $grunt, $assetsDir, $publicDir),
            sprintf('%s less --assets-dir=%s --public-dir=%s', $grunt, $assetsDir, $publicDir)
        ], $output);

        return 0;
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
        $logger = $this->getContainer()->get('monolog.logger.diamante');

        $result = $process->run(function ($type, $buffer) use ($output, $logger) {
            if (Process::ERR != $type) {
                if ($output->getVerbosity() > 1) {
                    $output->write($buffer);
                }
            } else {
                if ($output->getVerbosity() > 1) {
                    $output->write('<error>' . $buffer . '<error>');
                    $logger->error($buffer);
                }
            }
        });

        if ($result) {
            $output->writeln("<error>Failed</error>");
            $this->getContainer()->get('monolog.logger.diamante')->error($process->getErrorOutput());
            throw new \RuntimeException('Building Diamante Front failed');
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

    protected function locateDependencies($dependencies)
    {
        $locator = new DependencyLocator();
        $locatedDependencies = [];
        if (!is_array($dependencies)) {
            $dependencies = array($dependencies);
        }

        foreach ($dependencies as $dependency) {
                $result = $locator->locate($dependency);
                $locatedDependencies[$dependency] = $result;
        }

        return $locatedDependencies;
    }
}
