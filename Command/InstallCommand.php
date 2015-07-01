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
namespace Diamante\EmbeddedFormBundle\Command;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;
use Rhumsaa\Uuid\Console\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:embeddedform:install')
            ->setDescription('Install DiamanteDesk Embedded Forms');
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
        $extendExtension = $this->getContainer()->get('oro_entity_extend.migration.extension.extend');
        $nameGenerator = new ExtendDbIdentifierNameGenerator();
        $extendExtension->setNameGenerator($nameGenerator);
        $doctrine = $this->getContainer()->get('doctrine');
        $connection = $doctrine->getConnection();

        $platform    = $connection->getDatabasePlatform();
        $sm          = $connection->getSchemaManager();
        $schema      = new Schema(
            $sm->listTables(),
            $platform->supportsSequences() ? $sm->listSequences() : [],
            $sm->createSchemaConfig()
        );

        $origSchema = clone $schema;
        $extendExtension->addManyToOneRelation(
            $schema,
            'oro_embedded_form',
            'branch',
            'diamante_branch',
            'name',
            ['extend' => ['owner' => ExtendScope::OWNER_CUSTOM, 'is_extend' => true]]
        );

        try {
            $comparator = new Comparator();
            $schemaDiff = $comparator->compare($origSchema, $schema);
            $queries = $schemaDiff->toSql($platform);
            foreach ($queries as $query) {
                $connection->executeQuery($query);
            }

            $output->writeln('DiamanteDesk EmbeddedForm installed.');
        } catch (Exception $e) {
            $output->writeln("Failed ot install DiamanteDesk EmbeddedForm: " . $e->getMessage());
            return 255;
        }
        return 0;
    }
}
