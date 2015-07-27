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

namespace Diamante\AutomationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Diamante\AutomationBundle\Rule\Engine\EngineImpl;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\AutomationBundle\Rule\Repository\RepositoryProviderImpl;

class RuleCommand extends ContainerAwareCommand
{
    /**
     * @var EngineImpl
     */
    private $engine;

    /**
     * @var RepositoryProviderImpl
     */
    private $repositoryProvider;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diamante:automation:rule')
            ->setDescription('Run business rules');
    }

    /**
     * Initializes parameters required for business rules process
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->engine  = $this->getContainer()->get('diamante_automation.engine');
        $this->repositoryProvider = $this->getContainer()->get('diamante_automation.repository.provider');
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
            $repositories = $this->repositoryProvider->getRepositories();

            /** @var DoctrineGenericRepository $repository */
            foreach ($repositories as $repository) {
                foreach ($repository->findAll() as $entity) {
                    $fact = $this->engine->createFact($entity);
                    $result = $this->engine->check($fact, EngineImpl::MODE_BUSINESS);

                    if ($result) {
                        $this->engine->runAgenda();
                    }

                    $this->engine->reset();
                }
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return 255;
        }

        $output->writeln("Success!" . "\n");
        return 0;
    }
}
