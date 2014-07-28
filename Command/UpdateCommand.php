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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class UpdateCommand extends BaseCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:update')
            ->setDescription('Update Diamante Desk');
    }

    /**
     * Executes update process
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->write("Clearing cache... \n");
            $this->runExistingCommand('cache:clear', $output);
            $output->write('Done');

            $output->write("Updating DB schema... \n");
            $this->updateDbSchema();
            $output->writeln('Done');

            $output->write("Updating navigation... \n");
            $this->updateNavigation($output);
            $output->writeln('Done');

            $output->write('Installing assets...');
            $this->assetsInstall($output);
            $this->asseticDump($output, array(
                '--no-debug' => true,
            ));
            $output->write('Done');


            $output->write("Updating assets...\n");
            $this->updateAssets($output);
            $output->writeln('Done');

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return;
        }

        $output->writeln('Updated!');
    }

    /**
     * Updates DB Schema. Changes from Diamante only will be applied for current schema. Other bundles updating skips
     * @throws \Exception if there are no changes in entities
     */
    protected function updateDbSchema()
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

        $sql = $schemaTool->getUpdateSchemaSql($entitiesMetadata);
        $sql2 = $schemaTool->getUpdateSchemaSql(array());

        $toUpdate = array_diff($sql, $sql2);

        if (empty($toUpdate)) {
            throw new \Exception('No new updates found. Diamante Desk is up to date!');
        }

        $conn = $em->getConnection();

        foreach ($toUpdate as $sql) {
            $conn->executeQuery($sql);
        }
    }
}
