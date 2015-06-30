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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\TableHelper;

class InstallCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:install')
            ->setDescription('Install Diamante Desk Bundles');
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
            $this->runExistingCommand('oro:install', $output, array('--timeout' => 0));
            $this->runExistingCommand('diamante:desk:install', $output);
            $this->runExistingCommand('diamante:user:install', $output);
            $this->runExistingCommand('diamante:front:build', $output, array('--with-assets-dependencies'));
            $this->runExistingCommand('oro:assets:install', $output, array(
                'target' => './',
                '--exclude' => $this->listBundlesToExcludeInAssetsInstall()
            ));
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

    /**
     * @param OutputInterface $output
     *
     * @return InstallCommand
     * @throws \RuntimeException
     */
    protected function checkStep(OutputInterface $output)
    {
        $output->writeln('<info>Oro requirements check:</info>');

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
     * Render requirements table
     *
     * @param array           $collection
     * @param string          $header
     * @param OutputInterface $output
     */
    protected function renderTable(array $collection, $header, OutputInterface $output)
    {
        /** @var TableHelper $table */
        $table = $this->getHelperSet()->get('table');

        $table
            ->setHeaders(array('Check  ', $header))
            ->setRows(array());

        /** @var \Requirement $requirement */
        foreach ($collection as $requirement) {
            if ($requirement->isFulfilled()) {
                $table->addRow(array('OK', $requirement->getTestMessage()));
            } else {
                $table->addRow(
                    array(
                        $requirement->isOptional() ? 'WARNING' : 'ERROR',
                        $requirement->getHelpText()
                    )
                );
            }
        }

        $table->render($output);
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
