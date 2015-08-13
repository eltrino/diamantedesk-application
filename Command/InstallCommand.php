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
namespace Diamante\DeskBundle\Command;

use Diamante\DeskBundle\Model\Branch\Logo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private $kernelRootDir;

    /**
     * @var string
     */
    private $attachmentsDir;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:desk:install')
            ->setDescription('Install DiamanteDesk');
    }

    /**
     * Initializes parameters required for installation process
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->kernelRootDir  = $this->getContainer()->getParameter('kernel.root_dir');
        $this->attachmentsDir = $this->getContainer()->getParameter('diamante.attachment.directory.name');
        $this->filesystem     = $this->getContainer()->get('filesystem');
    }

    /**
     * Executes installation
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln('Creating Branch logo directory...');
            $this->createBranchLogoDirectory();
            $output->writeln('Done');

            $output->writeln('Creating attachments directory...');
            $this->createAttachmentsDirectory();
            $output->writeln('Done');

            $output->writeln('Installing DB schema...');
            $this->updateDbSchema();
            $output->writeln('Done');

            $this->loadData($output);

            $this->updateEntityConfig($output);

            $output->writeln('Updating navigation...');
            $this->updateNavigation($output);
            $output->writeln('Done');

            $output->writeln('Loading migration data');
            $this->loadDataFixtures($output);
            $output->writeln('Done');
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return 255;
        }

        $output->writeln('Installed!');
        return 0;
    }

    /**
     * Create Branch logo directory
     */
    protected function createBranchLogoDirectory()
    {
        $branchLogoDir = sprintf('%s%s', realpath($this->kernelRootDir .'/../web'), Logo::PATH_TO_LOGO_DIR);
        $this->createDirectory($branchLogoDir);
    }

    /**
     * Create Attachments directory
     */
    protected function createAttachmentsDirectory()
    {
        $attachmentsDir = $this->kernelRootDir . '/' . $this->attachmentsDir;
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

    /**
     * Load migrations from DataFixtures/ORM folder
     * @param OutputInterface $output
     */
    protected function loadDataFixtures(OutputInterface $output)
    {
        $bundlePath = $this->getContainer()->get('kernel')->locateResource('@DiamanteDeskBundle');

        $this->runExistingCommand('doctrine:fixtures:load', $output,
            array(
                '--fixtures'       => sprintf('%s/DataFixtures/ORM', $bundlePath),
                '--append'         => true,
                '--no-interaction' => true,
            )
        );
    }
}
