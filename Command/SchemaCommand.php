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
 


namespace Diamante\UserBundle\Command;


use Diamante\UserBundle\Entity\ApiUser;
use Diamante\UserBundle\Entity\DiamanteUser;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:user:schema')
            ->setDescription('Install Diamante User Bundle');
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
        try {
            $this->updateDbSchema();
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return 255;
        }
        return 0;
    }

    /**
     * Updates DB Schema.
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
            $em->getClassMetadata(ApiUser::getClassName()),
            $em->getClassMetadata(DiamanteUser::getClassName()),
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
            throw new \Exception('No new updates found. Diamante Api Bundle is up to date!');
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
}
