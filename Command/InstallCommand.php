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

class InstallCommand extends ContainerAwareCommand
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
        $this->kernelRootDir  = $this->getContainer()->getParameter('kernel.root_dir');
        $this->attachmentsDir = $this->getContainer()->getParameter('diamante.attachment.directory.name');
        $this->filesystem     = $this->getContainer()->get('filesystem');
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
            $this->installDbSchema();
            $output->writeln('Done');

            $this->loadData($output);

            $output->write('Updating navigation...');
            $this->updateNavigation($output);
            $output->writeln('Done');

            $this->assetsInstall($output);

            $this->asseticDump($output);

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
        $attachmentsDir = $this->kernelRootDir . '/' . $this->attachmentsDir;
        $this->createDirectory($attachmentsDir);
    }

    /**
     * Install DB Schema.
     * @throws \Exception if entities already installed
     */
    protected function installDbSchema()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $schemaTool = new SchemaTool($em);
        $entitiesMetadata = array(
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Branch::getClassName()),
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Ticket::getClassName()),
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Comment::getClassName()),
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Filter::getClassName()),
            $em->getClassMetadata(\Eltrino\DiamanteDeskBundle\Entity\Attachment::getClassName())
        );

        $toCreate = array();

        /** @var $entity \Doctrine\ORM\Mapping\ClassMetadata */
        foreach ($entitiesMetadata as $entity) {
            $skip = $em->getConnection()->getSchemaManager()->tablesExist(array($entity->getTableName()));
            if ($skip) {
                continue;
            }
            $toCreate[] = $entity;
        }

//        if (empty($toCreate)) {
//            throw new \Exception('All entities are created in schema. Please, run command diamante:update');
//        }

//        $schemaTool->createSchema($toCreate);
    }

    /**
     * Install assets
     * @param OutputInterface $output
     */
    protected function assetsInstall(OutputInterface $output)
    {
        $this->runExistingCommand('assets:install', $output);
    }

    /**
     * Dump assetic
     * @param OutputInterface $output
     */
    protected function asseticDump(OutputInterface $output)
    {
        $this->runExistingCommand('assetic:dump', $output);
    }

    /**
     * Load data fixtures
     * @param OutputInterface $output
     */
    protected function loadData(OutputInterface $output)
    {
        $this->runExistingCommand('oro:migration:data:load', $output);
    }

    /**
     * Run existing command in system
     * @param string $commandName
     * @param OutputInterface $output
     */
    private function runExistingCommand($commandName, OutputInterface $output)
    {
        $command = $this->getApplication()->find($commandName);

        $arguments = array(
            'command' => $commandName
        );

        $input = new ArrayInput($arguments);
        $command->run($input, $output);
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
     * Update oro navigation
     * @param OutputInterface $output
     */
    private function updateNavigation(OutputInterface $output)
    {
        $this->runExistingCommand('oro:navigation:init', $output);
    }
}
