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

use Composer\Question\StrictConfirmationQuestion;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Oro\Bundle\InstallerBundle\Command\AbstractCommand;
use Oro\Bundle\InstallerBundle\Command\InstallCommandInterface;
use Oro\Bundle\InstallerBundle\InstallerEvent;
use Oro\Bundle\InstallerBundle\InstallerEvents;
use Oro\Bundle\InstallerBundle\ScriptExecutor;
use Oro\Bundle\InstallerBundle\ScriptManager;
use Oro\Bundle\LocaleBundle\Command\UpdateLocalizationCommand;
use Oro\Bundle\LocaleBundle\DependencyInjection\OroLocaleExtension;
use Oro\Bundle\SecurityBundle\Command\LoadConfigurablePermissionCommand;
use Oro\Bundle\SecurityBundle\Command\LoadPermissionConfigurationCommand;
use Oro\Bundle\TranslationBundle\Command\OroLanguageUpdateCommand;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\InstallerBundle\CommandExecutor;
use Oro\Bundle\InstallerBundle\Command\Provider\InputOptionProvider;
use Symfony\Component\Console\Input\InputOption;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Process\Process;

class InstallCommand extends AbstractCommand implements InstallCommandInterface
{
    public const NAME = 'diamante:install';

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

    /** @var Process */
    private $assetsCommandProcess;

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
            ->addOption(
                'skip-assets',
                null,
                InputOption::VALUE_NONE,
                'Skip UI related commands during installation'
            )
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption(
                'sample-data',
                null,
                InputOption::VALUE_OPTIONAL,
                'Determines whether sample data need to be loaded or not'
            )
            ->addOption(
                'drop-database',
                null,
                InputOption::VALUE_NONE,
                'Database will be dropped and all data will be deleted.'
            )
            ->addOption(
                'skip-translations',
                null,
                InputOption::VALUE_NONE,
                'Determines whether translation data need to be loaded or not'
            )
            ->addOption(
                'skip-download-translations',
                null,
                InputOption::VALUE_NONE,
                'Determines whether translation data need to be downloaded or not'
            )
            ->addOption(
                UpdateLocalizationCommand::OPTION_LANGUAGE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Localization language'
            )
            ->addOption(
                UpdateLocalizationCommand::OPTION_FORMATTING_CODE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Localization formatting code'
            )
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force installation')
        ;
        
        parent::configure();
    }

    /**
     * Executes installation of all Diamante bundles
     *
     * @param InputInterface $input   An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info(sprintf('DiamanteDesk installation started at %s', date('Y-m-d H:i:s')));

        $forceInstall = $input->getOption('force');
        $skipAssets = $input->getOption('skip-assets');

        // if there is application is not installed or no --force option
        if ($this->isInstalled() && !$forceInstall) {
            return $this->alreadyInstalledMessageShow($output);
        }

        if ($forceInstall) {
            // if --force option we have to clear cache and set installed to false
            $this->updateInstalledFlag(false);
            $this->commandExecutor->runCommand(
                'cache:clear',
                [
                    '--no-optional-warmers' => true,
                    '--process-isolation'   => true,
                ]
            );
        }

        $output->writeln('<info>Installing DiamanteDesk.</info>');
        $output->writeln('');

        $this->checkRequirements($this->commandExecutor);

        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $event = new InstallerEvent($this, $input, $output, $this->commandExecutor);

        try {
            $this->prepareStep($input, $output);
            $eventDispatcher->dispatch(InstallerEvents::INSTALLER_BEFORE_DATABASE_PREPARATION, $event);
            if (!$skipAssets) {
                $this->startBuildAssetsProcess($input);
            }
            $this->loadDataStep($this->commandExecutor, $output);
            $eventDispatcher->dispatch(InstallerEvents::INSTALLER_AFTER_DATABASE_PREPARATION, $event);
            $output->writeln('<info>Administration setup finished.</info>');
            $this->finalStep($this->commandExecutor, $output, $input, $skipAssets);
            if (!$skipAssets) {
                $buildAssetsProcessExitCode = $this->getBuildAssetsProcessExitCode($output);
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return $this->commandExecutor->getLastCommandExitCode();

        }

        $this->successfullyInstalledMessageShow($input, $output);

        $this->logger->info(sprintf('DiamanteDesk installation finished at %s', date('Y-m-d H:i:s')));

        return $buildAssetsProcessExitCode ?? 0;
    }

    /**
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function alreadyInstalledMessageShow(OutputInterface $output)
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
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function successfullyInstalledMessageShow(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln(
            sprintf(
                '<info>DiamanteDesk has been successfully installed in <comment>%s</comment> mode.</info>',
                $input->getOption('env')
            )
        );
        if ('prod' != $input->getOption('env')) {
            $output->writeln(
                '<info>To run application in <comment>prod</comment> mode, ' .
                'please run <comment>cache:clear</comment> command with <comment>--env=prod</comment> parameter</info>'
            );
        }
        if ('prod' == $input->getOption('env')) {
            $output->writeln(
                '<info>Please run <comment>oro:api:doc:cache:clear</comment> command to warm-up ' .
                'API documentation cache</info>'
            );
        }
        $output->writeln(
            '<info>Ensure that at least one consumer service is running. ' .
            'Use the <comment>oro:message-queue:consume</comment> ' .
            'command to launch a consumer service instance. See ' .
            '<comment>' .
            'https://oroinc.com/orocrm/doc/current/install-upgrade/post-install-steps#activate-background-tasks' .
            '</comment> ' .
            'for more information.</info>'
        );
    }

    /**
     * @param InputInterface $input
     *
     * @throws \InvalidArgumentException
     */
    protected function validate(InputInterface $input)
    {
        $requiredParams = ['user-email', 'user-firstname', 'user-lastname', 'user-password'];
        $emptyParams    = [];

        foreach ($requiredParams as $param) {
            if (null === $input->getOption($param)) {
                $emptyParams[] = '--' . $param;
            }
        }

        if (!empty($emptyParams)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "The %s arguments are required in non-interactive mode",
                    implode(', ', $emptyParams)
                )
            );
        }

        $this->validateLocalizationOptions($input);
    }

    protected function checkRequirements(CommandExecutor $commandExecutor)
    {
        $commandExecutor->runCommand(
            'diamante:check-requirements',
            ['--ignore-errors' => true, '--verbose' => 2]
        );

        return $commandExecutor->getLastCommandExitCode();
    }

    /**
     * Drop schema, clear entity config and extend caches
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return \Diamante\DistributionBundle\Command\InstallCommand
     */
    protected function prepareStep(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('drop-database')) {
            $output->writeln('<info>Drop schema.</info>');
            $managers = $this->getContainer()->get('doctrine')->getManagers();
            foreach ($managers as $name => $manager) {
                if ($manager instanceof EntityManager) {
                    $tool = new SchemaTool($manager);
                    $tool->dropDatabase();
                }
            }
        }

        return $this;
    }

    /**
     * @param string $message
     *
     * @return callable
     */
    protected function getNotBlankValidator($message)
    {
        return function ($value) use ($message) {
            if (strlen(trim($value)) === 0) {
                throw new \Exception($message);
            }

            return $value;
        };
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
        $organizationNameValidator = function ($value) use (&$defaultOrganizationName) {
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
                'config_key'             => 'diamante_distribution.application_url',
            ]
        ];

        foreach ($options as $optionName => $optionData) {
            $configKey    = $optionData['config_key'];
            $defaultValue = $configManager->get($configKey);

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
                    '--timeout'           => $commandExecutor->getDefaultOption('process-timeout'),
                ]
            )
            ->runCommand(
                LoadPermissionConfigurationCommand::NAME,
                [
                    '--process-isolation' => true
                ]
            )
            ->runCommand(
                LoadConfigurablePermissionCommand::NAME,
                [
                    '--process-isolation' => true
                ]
            )
            ->runCommand(
                'oro:cron:definitions:load',
                [
                    '--process-isolation' => true
                ]
            )
            ->runCommand(
                'oro:workflow:definitions:load',
                [
                    '--process-isolation' => true
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
        $this->updateLocalization($commandExecutor);

        $commandExecutor->runCommand('diamante:desk:data');
        $commandExecutor->runCommand(
            'oro:migration:data:load',
            [
                '--bundles'           => ['DiamanteDistributionBundle'],
                '--process-isolation' => true,
                '--no-interaction'    => true,
            ]
        );

        $isDemo = $this->inputOptionProvider->get(
            'sample-data',
            'Load sample data (y/n)',
            false,
            [
                'class' => StrictConfirmationQuestion::class,
                'constructorArgs' => [false]
            ]
        );
        if ($isDemo) {
            // load demo fixtures
            $commandExecutor->runCommand(
                'oro:migration:data:load',
                [
                    '--process-isolation'  => true,
                    '--fixtures-type'      => 'demo',
                ]
            );
        }

        $output->writeln('');

        return $this;
    }

    /**
     * @param CommandExecutor $commandExecutor
     * @param OutputInterface $output
     * @param InputInterface $input
     * @param boolean $skipAssets
     *
     * @return \Diamante\DistributionBundle\Command\InstallCommand
     */
    protected function finalStep(
        CommandExecutor $commandExecutor,
        OutputInterface $output,
        InputInterface $input,
        $skipAssets
    ) {
        $output->writeln('<info>Preparing application.</info>');

        $assetsOptions = [];
        if ($input->hasOption('symlink') && $input->getOption('symlink')) {
            $assetsOptions['--symlink'] = true;
        }

        $this->processTranslations($input, $commandExecutor);

        // run installer scripts
        $this->processInstallerScripts($output, $commandExecutor);

        $this->updateInstalledFlag(date('c'));

        // clear the cache and set installed flag in DI container
        $cacheClearOptions = ['--process-isolation' => true];
        if ($commandExecutor->getDefaultOption('no-debug')) {
            $cacheClearOptions['--no-debug'] = true;
        }
        if ($input->getOption('env')) {
            $cacheClearOptions['--env'] = $input->getOption('env');
        }
        $commandExecutor->runCommand('cache:clear', $cacheClearOptions);

        if (!$skipAssets) {
            /**
             * Place this launch of command after the launch of 'assetic-dump' in BAP-16333
             */
            $commandExecutor->runCommand(
                'oro:translation:dump',
                [
                    '--process-isolation' => true,
                ]
            );
        }

        $output->writeln('');

        return $this;
    }

    /**
     * Update installed flag in parameters.yml
     *
     * @param bool|string $installed
     */
    protected function updateInstalledFlag($installed)
    {
        $dumper                        = $this->getContainer()->get('oro_installer.yaml_persister');
        $params                        = $dumper->parse();
        $params['system']['installed'] = $installed;
        $dumper->dump($params);
    }

    /**
     * Clears the state of all database checkers to make sure they will recheck the database state
     */
    protected function clearCheckDatabaseState()
    {
        $this->getContainer()->get('oro_entity.database_checker.state_manager')->clearState();
    }

    /**
     * Process installer scripts
     *
     * @param OutputInterface $output
     * @param CommandExecutor $commandExecutor
     */
    protected function processInstallerScripts(OutputInterface $output, CommandExecutor $commandExecutor)
    {
        $scriptExecutor = new ScriptExecutor($output, $this->getContainer(), $commandExecutor);
        /** @var ScriptManager $scriptManager */
        $scriptManager = $this->getContainer()->get('oro_installer.script_manager');
        $scriptFiles   = $scriptManager->getScriptFiles();
        if (!empty($scriptFiles)) {
            foreach ($scriptFiles as $scriptFile) {
                $scriptExecutor->runScript($scriptFile);
            }
        }
    }

    /**
     * @return bool
     */
    protected function isInstalled()
    {
        $isInstalled = $this->getContainer()->hasParameter('installed')
            && $this->getContainer()->getParameter('installed');

        return $isInstalled;
    }

    /**
     * @param InputInterface $input
     * @param CommandExecutor $commandExecutor
     */
    protected function processTranslations(InputInterface $input, CommandExecutor $commandExecutor)
    {
        if (!$input->getOption('skip-translations')) {
            if (!$input->getOption('skip-download-translations')) {
                $commandExecutor
                    ->runCommand(OroLanguageUpdateCommand::NAME, ['--process-isolation' => true, '--all' => true]);
            }
            $commandExecutor
                ->runCommand('oro:translation:load', ['--process-isolation' => true, '--rebuild-cache' => true]);
        }
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }

    /**
     * @param CommandExecutor $commandExecutor
     */
    protected function updateLocalization(CommandExecutor $commandExecutor)
    {
        $formattingCode = $this->getContainer()->getParameter(OroLocaleExtension::PARAMETER_FORMATTING_CODE);
        $language = $this->getContainer()->getParameter(OroLocaleExtension::PARAMETER_LANGUAGE);

        $options = [
            'formatting-code' => [
                'label' => 'Formatting Code',
                'options' => [
                    'constructorArgs' => [$formattingCode],
                    'settings' => [
                        'validator' => [
                            function ($value) {
                                $this->validateFormattingCode($value);
                                return $value;
                            }
                        ]
                    ]
                ],
                'defaultValue' => $formattingCode
            ],
            'language' => [
                'label' => 'Language',
                'options' => [
                    'constructorArgs' => [$language],
                    'settings' => [
                        'validator' => [
                            function ($value) {
                                $this->validateLanguage($value);
                                return $value;
                            }
                        ]
                    ]
                ],
                'defaultValue' => $language
            ]
        ];

        $commandExecutor->runCommand(
            UpdateLocalizationCommand::NAME,
            array_merge(
                [
                    '--process-isolation' => true
                ],
                $this->getCommandParametersFromOptions($options)
            )
        );
    }

    /**
     * @param InputInterface $input
     */
    private function validateLocalizationOptions(InputInterface $input): void
    {
        $formattingCode = $input->getOption('formatting-code');
        if ($formattingCode) {
            $this->validateFormattingCode($formattingCode);
        }

        $language = (string)$input->getOption('language');
        if ($language) {
            $this->validateLanguage($language);
        }
    }

    /**
     * @param string $locale
     * @throws \InvalidArgumentException
     */
    private function validateFormattingCode(string $locale): void
    {
        $locales = array_keys(Intl::getLocaleBundle()->getLocaleNames());
        if (!in_array($locale, $locales, true)) {
            throw new \InvalidArgumentException($this->getExceptionMessage('formatting', $locale, $locales));
        }
    }

    /**
     * @param string $language
     * @throws \InvalidArgumentException
     */
    private function validateLanguage(string $language)
    {
        $locales = Intl::getLocaleBundle()->getLocales();
        if (!in_array($language, $locales, true)) {
            throw new \InvalidArgumentException($this->getExceptionMessage('language', $language, $locales));
        }
    }

    /**
     * @param string $optionName
     * @param string $localeCode
     * @param array $availableLocaleCodes
     * @return string
     */
    private function getExceptionMessage(string $optionName, string $localeCode, array $availableLocaleCodes):string
    {
        $exceptionMessage = sprintf('"%s" is not a valid %s code!', $localeCode, $optionName);
        $alternatives = $this->getAlternatives($localeCode, $availableLocaleCodes);
        if ($alternatives) {
            $exceptionMessage .= sprintf("\nDid you mean %s?\n", $alternatives);
        }

        return $exceptionMessage;
    }

    /**
     * @param array $options
     * @return array
     */
    private function getCommandParametersFromOptions(array $options): array
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

    /**
     * @param string $name
     * @param array $items
     * @return string
     */
    private function getAlternatives(string $name, array $items): string
    {
        $alternatives = [];
        foreach ($items as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= strlen($name) / 2 || false !== strpos($item, $name)) {
                $alternatives[$item] = $lev;
            }
        }
        asort($alternatives);

        return implode(', ', array_keys($alternatives));
    }

    /**
     * @param InputInterface $input
     */
    private function startBuildAssetsProcess(InputInterface $input): void
    {
        $phpBinaryPath = CommandExecutor::getPhpExecutable();

        $command = [
            $phpBinaryPath,
            'bin/console',
            'oro:assets:install'
        ];

        if ($input->hasOption('symlink') && $input->getOption('symlink')) {
            $command[] = '--symlink';
        }

        if ($input->getOption('env')) {
            $command[] = sprintf('--env=%s', $input->getOption('env'));
        }

        $this->assetsCommandProcess = new Process(
            $command,
            realpath($this->getContainer()->getParameter('kernel.project_dir'))
        );
        $this->assetsCommandProcess->setTimeout(null);
        $this->assetsCommandProcess->start();
    }

    /**
     * @param OutputInterface $output
     * @return int||null
     */
    private function getBuildAssetsProcessExitCode(OutputInterface $output): ?int
    {
        if (!$this->assetsCommandProcess) {
            return 0;
        }

        if (!$this->assetsCommandProcess->isTerminated()) {
            $this->assetsCommandProcess->wait();
        }

        if ($this->assetsCommandProcess->isSuccessful()) {
            $output->writeln('Assets has been installed successfully');
            $output->writeln($this->assetsCommandProcess->getOutput());
        } else {
            $output->writeln('Assets has not been installed! Please run "php bin/console oro:assets:install".');
            $output->writeln('Error during install assets:');
            $output->writeln($this->assetsCommandProcess->getErrorOutput());
        }

        return $this->assetsCommandProcess->getExitCode();
    }
}
