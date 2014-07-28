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
namespace Eltrino\DiamanteDeskBundle\Command;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallCommand extends BaseCommand
{
    /**
     * @var string
     */
    private $kernelRootDir;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:install')
            ->setDescription('Install Diamante Desk');
    }

    /**
     * Initializes parameters required for installation process
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->kernelRootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $this->filesystem    = $this->getContainer()->get('filesystem');
    }

    /**
     * Executes installation
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->write('Creating Branch logo directory...');
            $this->createBranchLogoDirectory();
            $output->writeln('Done');

            $output->write('Creating attachments directory...');
            $this->createAttachmentsDirectory();
            $output->writeln('Done');

            $output->write('Installing DB schema...');
            $this->updateDbSchema();
            $output->writeln('Done');

            $this->loadData($output);

            $output->write('Updating navigation...');
            $this->updateNavigation($output);
            $output->writeln('Done');

            $this->assetsInstall($output);

            $this->asseticDump($output, array(
                '--no-debug' => true,
            ));

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return;
        }

        $output->writeln('Installed!');
        return 0;
    }

    /**
     * Create Branch logo directory
     */
    protected function createBranchLogoDirectory()
    {
        $branchLogoDir = realpath($this->kernelRootDir .'/../web')
            . \Eltrino\DiamanteDeskBundle\Branch\Model\Logo::PATH_TO_LOGO_DIR;

        $this->createDirectory($branchLogoDir);
    }

    /**
     * Create Attachments directory
     */
    protected function createAttachmentsDirectory()
    {
        $attachmentsDir = $this->kernelRootDir . '/'
            . \Eltrino\DiamanteDeskBundle\Attachment\Model\Attachment::ATTACHMENTS_DIRECTORY;

        $this->createDirectory($attachmentsDir);
    }

    /**
     * Create directory
     * @param string $directory
     */
    private function createDirectory($directory)
    {
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }
        $directory = new \SplFileInfo($directory);
        if (!$directory->isWritable()) {
            $this->filesystem->chmod($directory->getRealPath(), 0777);
        }
    }

    /**
     * Load data fixtures
     * @param OutputInterface $output
     */
    private function loadData(OutputInterface $output)
    {
        $this->runExistingCommand('oro:migration:data:load', $output);
    }
}
