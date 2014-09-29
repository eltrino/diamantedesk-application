<?php

namespace Diamante\DeskBundle\Command;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesPurgeCommand extends ContainerAwareCommand
{
    const CODE_FAILURE     = 255;
    const CODE_SUCCESS  = 0;
    /**
     * @var string
     */
    private $kernelDir;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var \Oro\Bundle\EntityBundle\ORM\OroEntityManager
     */
    private $entityManager;

    /**
     * Configuration of current command
     */
    protected function configure()
    {
        $this->setName('diamante:fixtures:purge')
            ->setDescription('Purge Test Fixtures');
    }

    /**
     * Initialization of data required to run current command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->kernelDir = $this->getContainer()->getParameter('kernel.root_dir');
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->output = $output;
    }

    /**
     * Execution of command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $diamanteTables = $this->getTablesList();

            if (!empty($diamanteTables)) {
                $this->purgeTables($diamanteTables);
            } else {
                throw new \Exception('Unable to get diamante tables list to purge from.');
            }

        } catch (\Exception $e) {
            $output->writeln('Failed to purge test fixtures. Error: ' . $e->getMessage());
            return self::CODE_FAILURE;
        }

        $output->writeln('Test fixtures purged successfully');
        return self::CODE_SUCCESS;
    }

    /**
     * Get list of Diamante tables to purge data from
     *
     * @return array
     */
    protected function getTablesList()
    {

        $entitiesMetadata = array(
            $this->entityManager->getClassMetadata(\Diamante\DeskBundle\Entity\Branch::getClassName()),
            $this->entityManager->getClassMetadata(\Diamante\DeskBundle\Entity\Ticket::getClassName()),
            $this->entityManager->getClassMetadata(\Diamante\DeskBundle\Entity\Comment::getClassName()),
            $this->entityManager->getClassMetadata(\Diamante\DeskBundle\Entity\Attachment::getClassName())
        );

        $toPurge = array();

        /** @var $entity \Doctrine\ORM\Mapping\ClassMetadata */
        foreach ($entitiesMetadata as $entity) {
            $tableName = $entity->getTableName();

            $toPurge[] = $tableName;
        }

        return $toPurge;
    }

    /**
     * Perform the actual purge by truncating each of tables from the provided list
     * Throws an Exception (in case if anything goes wrong) which stops the process of truncating to prevent
     * the possible damage to DB
     *
     * @param array $tablesList
     * @throws \Exception
     */
    protected function purgeTables(array $tablesList)
    {
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tablesList as $table) {
            $query = $dbPlatform->getTruncateTableSql($table);
            try {
                $this->output->writeln('Purging data from ' . $table);
                $connection->executeUpdate($query);
            } catch (\Exception $e) {
                $this->output->writeln('Error purging data from \'' . $table . '\'. Error: ' . $e->getMessage());
                $connection->query('SET FOREIGN_KEY_CHECKS=1;');

                throw $e;
            }
        }

        $connection->query('SET FOREIGN_KEY_CHECKS=1;');
    }

}
