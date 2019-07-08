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

namespace Diamante\DistributionBundle\Command;

use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\InstallerBundle\Command\InstallCommand as OroInstallCommand;
use Oro\Bundle\InstallerBundle\CommandExecutor;
use Oro\Bundle\InstallerBundle\Command\Provider\InputOptionProvider;
use Symfony\Component\Console\Input\InputOption;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * @TODO ORO 2.0 Database schema dropped successfully! executes three times but looks like nothing dropped
 *
 *
 */
class InstallCommand extends OroInstallCommand
{
    /**
     * @var CommandExecutor
     */
    protected $commandExecutor;

    /**
     * @var Logger
     *
     */
    protected $logger;

    /**
     * @var InputOptionProvider
     */
    protected $inputOptionProvider;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->logger = $this->getContainer()->get('monolog.logger.diamante');
        $this->inputOptionProvider = new InputOptionProvider($output, $input, $this->getHelperSet()->get('question'));

        if (false === $input->isInteractive()) {
            $this->validate($input);
        }

        $this->commandExecutor = $this->getCommandExecutor($input, $output);
    }

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
            ->addOption('user-password', null, InputOption::VALUE_OPTIONAL, 'User password')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force installation')
            ->addOption(
                'drop-database',
                null,
                InputOption::VALUE_NONE,
                'Database will be dropped and all data will be deleted.'
            )
            ->addOption(
                'timeout',
                null,
                InputOption::VALUE_OPTIONAL,
                'Timeout for child command execution',
                CommandExecutor::DEFAULT_TIMEOUT
            )
            ->addOption(
                'skip-assets',
                null,
                InputOption::VALUE_NONE,
                'Skip UI related commands during installation'
            )
            ->addOption(
                'force-debug',
                null,
                InputOption::VALUE_NONE,
                'Forces launching of child commands in debug mode. By default they are launched with --no-debug'
            )->addOption(
                'skip-translations',
                null,
                InputOption::VALUE_NONE,
                'Determines whether translation data need to be loaded or not'
            );
    }

    /**
     * Executes installation of all Diamante bundles
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger
            ->info(sprintf('DiamanteDesk installation started at %s', date('Y-m-d H:i:s')));

        $forceInstall = $input->getOption('force');

        // if there is application is not installed or no --force option
        $isInstalled = $this->getContainer()->hasParameter('installed')
            && $this->getContainer()->getParameter('installed');

        if ($isInstalled && !$forceInstall) {
            return $this->alreadyInstalledMessage($output);
        }

        if ($forceInstall) {
            // if --force option we have to clear cache and set installed to false
            $this->updateInstalledFlag(false);
            $this->commandExecutor->runCommand(
                'cache:clear',
                [
                    '--no-optional-warmers' => true,
                    '--process-isolation'   => true
                ]
            );
        }

        $output->writeln('<info>Installing DiamanteDesk.</info>');

        $this->commandExecutor->runCommand(
            'diamante:check-requirements',
            [
                '--process-isolation' => true,
                '-vv' => true,
            ]
        );
        $this->prepareStep($input, $output)
                ->loadDataStep($this->commandExecutor, $output);


        $output->writeln('<info>Administration setup.</info>');
        $this->finalStep($this->commandExecutor, $output, $input, $input->getOption('skip-assets'));

        $output->writeln(
            sprintf(
                '<info>DiamanteDesk has been successfully installed in <comment>%s</comment> mode.</info>',
                $input->getOption('env')
            )
        );

        if ('prod' != $input->getOption('env')) {
            $output->writeln(
                '<info>To run application in <comment>prod</comment> mode, ' .
                'please run <comment>cache:clear</comment> command with <comment>--env prod</comment> parameter</info>'
            );
        }

        $this->logger
            ->info(sprintf('DiamanteDesk installation finished at %s', date('Y-m-d H:i:s')));

        return 0;
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
                    '--timeout'           => $commandExecutor->getDefaultOption('process-timeout')
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
                    '--exclude'           => ['DiamanteDistributionBundle']
                ]
            );

        $output->writeln('');
        $output->writeln('<info>Administration setup.</info>');

        $this->updateSystemSettings();
        $this->updateOrganization($commandExecutor);
        $this->updateUser($commandExecutor);

        $commandExecutor->runCommand('diamante:desk:data');

        $commandExecutor->runCommand(
            'oro:migration:data:load',
            [
                '--bundles' => ['DiamanteDistributionBundle'],
                '--process-isolation' => true,
                '--no-interaction'    => true,
            ]
        );


        $output->writeln('');

        return $this;
    }

    /**
     * Update the administrator user
     *
     * @param CommandExecutor $commandExecutor
     */
    protected function updateUser(CommandExecutor $commandExecutor)
    {
        $emailValidator     = $this->getNotBlankValidator('The email must be specified');
        $firstNameValidator = $this->getNotBlankValidator('The first name must be specified');
        $lastNameValidator  = $this->getNotBlankValidator('The last name must be specified');
        $passwordValidator  = function ($value) {
            if (strlen(trim($value)) < 2) {
                throw new \Exception('The password must be at least 2 characters long');
            }

            return $value;
        };

        $options = [
            'user-name'      => [
                'label'                  => 'Username',
                'options'                => [
                    'constructorArgs' => [LoadAdminUserData::DEFAULT_ADMIN_USERNAME]
                ],
                'defaultValue'           => LoadAdminUserData::DEFAULT_ADMIN_USERNAME,
            ],
            'user-email'     => [
                'label'                  => 'Email',
                'options'                => ['settings' => ['validator' => [$emailValidator]]],
                'defaultValue'           => null,
            ],
            'user-firstname' => [
                'label'                  => 'First name',
                'options'                => ['settings' => ['validator' => [$firstNameValidator]]],
                'defaultValue'           => null,
            ],
            'user-lastname'  => [
                'label'                  => 'Last name',
                'options'                => ['settings' => ['validator' => [$lastNameValidator]]],
                'defaultValue'           => null,
            ],
            'user-password'  => [
                'label'                  => 'Password',
                'options'                => ['settings' => ['validator' => [$passwordValidator], 'hidden' => [true]]],
                'defaultValue'           => null,
            ],
        ];

        //$this->commandExecutor->runCommand('cache:clear');  @see DIAM-1923

        $commandExecutor->runCommand(
            'oro:user:update',
            array_merge(
                [
                    'user-name'           => LoadAdminUserData::DEFAULT_ADMIN_USERNAME,
                    '--process-isolation' => true
                ],
                $this->getCommandParametersFromOptions($options)
            )
        );
    }

    /**
     * Update the organization
     *
     * @param CommandExecutor $commandExecutor
     */
    protected function updateOrganization(CommandExecutor $commandExecutor)
    {
        /** @var ConfigManager $configManager */
        $configManager             = $this->getContainer()->get('oro_config.global');
        $defaultOrganizationName   = $configManager->get('diamante_distribution.organization_name');
        $organizationNameValidator = function($value) use (&$defaultOrganizationName) {
            $len = strlen(trim($value));
            if ($len === 0 && empty($defaultOrganizationName)) {
                throw new \Exception('The organization name must not be empty');
            }
            if ($len > 15) {
                throw new \Exception('The organization name must be not more than 15 characters long');
            }
            return $value;
        };

        $options = [
            'organization-name' => [
                'label'                  => 'Organization name',
                'options'                => [
                    'constructorArgs' => [$defaultOrganizationName],
                    'settings' => ['validator' => [$organizationNameValidator]]
                ],
                'defaultValue'           => $defaultOrganizationName,
            ]
        ];

        $commandExecutor->runCommand(
            'oro:organization:update',
            array_merge(
                [
                    'organization-name' => 'default',
                    '--process-isolation' => true,
                ],
                $this->getCommandParametersFromOptions($options)
            )
        );
    }

    /**
     * Update system settings such as app url, company name and short name
     */
    protected function updateSystemSettings()
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->getContainer()->get('oro_config.global');

        $options       = [
            'application-url' => [
                'label'                  => 'Application URL',
                'config_key'             => 'oro_ui.application_url',
            ]
        ];

        foreach ($options as $optionName => $optionData) {
            $configKey    = $optionData['config_key'];
            $defaultValue = $configManager->get('diamante_distribution.application_url');
            $value = $this->inputOptionProvider->get(
                $optionName,
                $optionData['label'],
                $defaultValue,
                ['constructorArgs' => [$defaultValue]]
            );

            // update setting if it's not empty and not equal to default value
            if (!empty($value) && $value !== $defaultValue) {
                $configManager->set($configKey, $value);
            }
        }

        $configManager->flush();
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    protected function alreadyInstalledMessage(OutputInterface $output)
    {
        $output->writeln('<comment>ATTENTION</comment>: DiamanteDesk already installed.');
        $output->writeln(
            'To proceed with install - run command with <info>--force</info> option:'
        );
        $output->writeln(sprintf('    <info>%s --force</info>', $this->getName()));
        $output->writeln(
            'To reinstall over existing database - run command with <info>--force --drop-database</info> options:'
        );
        $output->writeln(sprintf('    <info>%s --force --drop-database</info>', $this->getName()));
        $output->writeln(
            '<comment>ATTENTION</comment>: All data will be lost. ' .
            'Database backup is highly recommended before executing this command.'
        );
        $output->writeln('');

        return 255;
    }

    /**
     * @param array $options
     * @return array
     */
    private function getCommandParametersFromOptions(array $options)
    {
        $commandParameters = [];
        foreach ($options as $optionName => $optionData) {
            $commandParameters['--' . $optionName] = $this->inputOptionProvider->get(
                $optionName,
                $optionData['label'],
                $optionData['defaultValue'],
                $optionData['options']
            );
        }

        return $commandParameters;
    }
}
