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
namespace Diamante\DistributionBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\InstallerBundle\Command\InstallCommand as OroInstallCommand;
use Oro\Bundle\InstallerBundle\CommandExecutor;
use Oro\Bundle\InstallerBundle\Command\Provider\InputOptionProvider;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends OroInstallCommand
{
    /**
     * @var CommandExecutor
     */
    protected $commandExecutor;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:install')
            ->setDescription('Install Diamante Desk Bundles')
            ->addOption('application-url', null, InputOption::VALUE_OPTIONAL, 'Application URL')
            ->addOption('organization-name', null, InputOption::VALUE_OPTIONAL, 'Organization name')
            ->addOption('user-name', null, InputOption::VALUE_OPTIONAL, 'User name')
            ->addOption('user-email', null, InputOption::VALUE_OPTIONAL, 'User email')
            ->addOption('user-firstname', null, InputOption::VALUE_OPTIONAL, 'User first name')
            ->addOption('user-lastname', null, InputOption::VALUE_OPTIONAL, 'User last name')
            ->addOption('user-password', null, InputOption::VALUE_OPTIONAL, 'User password');
    }

    /**
     * Executes installation of all Diamante bundles
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('monolog.logger.diamante')
            ->info(sprintf('DiamanteDesk installation started at %s', date('Y-m-d H:i:s')));
        try {
            $this->checkStep($output);
            $this->oroInit($output, $input);
            $this->oroInstall($input, $output);
            $this->runExistingCommand('diamante:desk:install', $output);
            $this->runExistingCommand('diamante:user:install', $output);
            $this->runExistingCommand('diamante:embeddedform:install', $output);
            $this->runExistingCommand('diamante:front:build', $output, array('--with-assets-dependencies' => true));
            $this->runExistingCommand('oro:assets:install', $output, array(
                    'target' => './',
                    '--exclude' => $this->listBundlesToExcludeInAssetsInstall()
                ));
            $this->oroAdministrationSetup($output);
        } catch (\Exception $e) {
            $this->getContainer()->get('monolog.logger.diamante')
                ->error(sprintf('Installation failed with error: %s', $e->getMessage()));
            $output->writeln($e->getMessage());
            return 255;
        }
        $this->getContainer()->get('monolog.logger.diamante')
            ->info(sprintf('DiamanteDesk installation finished at %s', date('Y-m-d H:i:s')));
        return 0;
    }

    protected function oroInit($output, $input)
    {
        $this->inputOptionProvider = new InputOptionProvider($output, $input, $this->getHelperSet()->get('dialog'));

        $this->commandExecutor = new CommandExecutor(
            $input->hasOption('env') ? $input->getOption('env') : null,
            $output,
            $this->getApplication(),
            $this->getContainer()->get('oro_cache.oro_data_cache_manager')
        );
        $this->commandExecutor->setDefaultTimeout(0);
    }

    protected function oroInstall(InputInterface $input, OutputInterface $output)
    {
        $this->prepareStep($this->commandExecutor)
            ->loadDataStep($this->commandExecutor, $output)
            ->finalStep($this->commandExecutor, $output, $input);
    }

    /**
     * @param CommandExecutor $commandExecutor
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function loadDataStep(CommandExecutor $commandExecutor, OutputInterface $output)
    {
        $output->writeln('<info>Setting up database.</info>');

        $commandExecutor
            ->runCommand(
                'oro:migration:load',
                [
                    '--force'             => true,
                    '--process-isolation' => true,
                    '--timeout'           => $commandExecutor->getDefaultTimeout()
                ]
            )
            ->runCommand(
                'oro:workflow:definitions:load',
                [
                    '--process-isolation' => true,
                ]
            )
            ->runCommand(
                'oro:process:configuration:load',
                [
                    '--process-isolation' => true
                ]
            )
            ->runCommand(
                'oro:migration:data:load',
                [
                    '--process-isolation' => true,
                    '--no-interaction'    => true,
                ]
            );

        $output->writeln('');

        return $this;
    }

    protected function oroAdministrationSetup(OutputInterface $output)
    {
        $output->writeln('<info>Administration setup.</info>');

        $this->updateSystemSettings();
        $this->updateOrganization($this->commandExecutor);
        $this->updateUser($this->commandExecutor);
    }

    /**
     * @param OutputInterface $output
     *
     * @return InstallCommand
     * @throws \RuntimeException
     */
    protected function checkStep(OutputInterface $output)
    {
        $output->writeln('<info>Diamante requirements check:</info>');

        if (!class_exists('OroRequirements')) {
            require_once $this->getContainer()->getParameter('kernel.root_dir')
                . DIRECTORY_SEPARATOR
                . 'OroRequirements.php';
        }

        if (!class_exists('DiamanteDeskRequirements')) {
            require_once $this->getContainer()->getParameter('kernel.root_dir')
                . DIRECTORY_SEPARATOR
                . 'DiamanteDeskRequirements.php';
        }

        $collection = new \OroRequirements();
        $diamanteDeskCollection = new \DiamanteDeskRequirements();

        $this->renderTable($collection->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getPhpIniRequirements(), 'PHP settings', $output);
        $this->renderTable($collection->getOroRequirements(), 'Oro specific requirements', $output);
        $this->renderTable(
            $diamanteDeskCollection->getDiamanteDeskRequirements(),
            'DiamanteDesk requirements',
            $output
        );
        $this->renderTable($collection->getRecommendations(), 'Optional recommendations', $output);

        if (count($collection->getFailedRequirements())) {
            throw new \RuntimeException(
                'Some system requirements are not fulfilled. Please check output messages and fix them.'
            );
        }

        $output->writeln('');

        return $this;
    }

    /**
     * Run existing command in system
     * @param string $commandName
     * @param OutputInterface $output
     * @param array $parameters
     */
    protected function runExistingCommand($commandName, OutputInterface $output, array $parameters = array())
    {
        $command = $this->getApplication()->find($commandName);

        $arguments = array(
            'command' => $commandName
        );

        $arguments = array_merge($arguments, $parameters);

        $input = new ArrayInput($arguments);
        $command->run($input, $output);
    }

    /**
     * @return array list of registered bundles
     */
    private function listBundlesToExcludeInAssetsInstall()
    {
        $bundles = $this->getContainer()->getParameter('kernel.bundles');
        if (isset($bundles['DiamanteFrontBundle'])) {
            unset($bundles['DiamanteFrontBundle']);
        }
        return array_keys($bundles);
    }
}
