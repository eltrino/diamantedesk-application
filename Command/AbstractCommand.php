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

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;

abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * Updates DB Schema. Changes from Diamante only will be applied for current schema. Other bundles updating skips
     * @throws \Exception if there are no changes in entities
     */
    protected function updateDbSchema()
    {
        /**
         * @var $em \Doctrine\ORM\EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $event = $em->getEventManager();
        $sm = $em->getConnection()->getSchemaManager();
        $allMetadata = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $entitiesMetadata = array(
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\Branch::getClassName()),
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\Ticket::getClassName()),
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\Comment::getClassName()),
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\Filter::getClassName()),
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\Attachment::getClassName()),
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\BranchEmailConfiguration::getClassName()),
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\MessageReference::getClassName()),
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\TicketHistory::getClassName()),
            $em->getClassMetadata(\Diamante\DeskBundle\Entity\WatcherList::getClassName()),
        );

        $event->disableListeners();
        $currentSchema = $sm->createSchema();
        $schemaFromMetadata = $schemaTool->getSchemaFromMetadata($allMetadata);
        $entitiesSchema = $schemaTool->getSchemaFromMetadata($entitiesMetadata);
        $entitiesTables = $entitiesSchema->getTables();
        $entitiesTableName = array_keys($entitiesTables);

        $currentDiamanteSchema = $this->getTargetSchema($currentSchema, $entitiesTableName);
        $diamanteSchemaFromMetadata = $this->getTargetSchema($schemaFromMetadata, $entitiesTableName);

        $comparator = new Comparator();
        $diff = $comparator->compare($currentDiamanteSchema, $diamanteSchemaFromMetadata);
        $toUpdate = $diff->toSql($em->getConnection()->getDatabasePlatform());

        if (empty($toUpdate)) {
            throw new \Exception('No new updates found. DiamanteDesk is up to date!');
        }

        $conn = $em->getConnection();

        foreach ($toUpdate as $sql) {
            $conn->executeQuery($sql);
        }
    }

    /**
     * Return schema with Diamante tables
     * @param Schema $schema
     * @param array $entitiesTableName
     *
     * @return Schema
     */
    protected function getTargetSchema(Schema $schema, array $entitiesTableName)
    {
        $allTables = $schema->getTables();
        $targetTables = array();

        foreach ($allTables as $tableName => $table) {
            if (in_array($tableName, $entitiesTableName)) {
                $targetTables[$tableName] = $table;
            }
        }

        return new Schema($targetTables);
    }

    /**
     * Update oro navigation
     * @param OutputInterface $output
     */
    protected function updateNavigation(OutputInterface $output)
    {
        $this->runExistingCommand('oro:navigation:init', $output);
    }

    /**
     * Update oro entity-config
     *
     * @param OutputInterface $output
     */
    protected function updateEntityConfig(OutputInterface $output)
    {
        $this->runExistingCommand('oro:entity-config:update', $output);
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
}
