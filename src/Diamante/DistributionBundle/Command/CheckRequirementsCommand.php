<?php

namespace Diamante\DistributionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckRequirementsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('diamante:check-requirements')
            ->setDescription('Check if Diamante Application meets all requirements')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command checks that the application meets the system requirements.

By default this command shows only errors, but you can specify the verbosity level to see warnings
and information messages, e.g.:

  <info>php %command.full_name% -v</info>
or
  <info>php %command.full_name% -vv</info>

The process exit code will be 0 if all requirements are met and 1 if at least one requirement is not fulfilled.
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputRequirements($output);
    }

    /**
     * @param OutputInterface $output
     */
    protected function outputRequirements(OutputInterface $output)
    {
        $output->writeln('<info>Requirements check:</info>');

        $requirements = $this->getRequirements();

        $this->renderTable($requirements->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($requirements->getPhpIniRequirements(), 'PHP settings', $output);
        $this->renderTable($requirements->getOroRequirements(), 'Oro specific requirements', $output);
        $this->renderTable($requirements->getDiamanteDeskRequirements(), 'DiamanteDesk requirements', $output);
        $this->renderTable($requirements->getRecommendations(), 'Optional recommendations', $output);

        if (count($requirements->getFailedRequirements())) {
            throw new \RuntimeException(
                'Some system requirements are not fulfilled. Please check output messages and fix them.'
            );
        }

        $output->writeln('');
    }

    /**
     * @return \DiamanteDeskRequirements
     */
    protected function getRequirements()
    {
        if (!class_exists('DiamanteDeskRequirements')) {
            require_once $this->getContainer()->getParameter('kernel.project_dir')
                . DIRECTORY_SEPARATOR
                . 'var'
                . DIRECTORY_SEPARATOR
                . 'DiamanteDeskRequirements.php';
        }

        return new \DiamanteDeskRequirements();
    }

    /**
     * @param \Requirement[] $requirements
     * @param string $header
     * @param OutputInterface $output
     */
    protected function renderTable(array $requirements, $header, OutputInterface $output)
    {
        $rows = [];
        $verbosity = $output->getVerbosity();
        foreach ($requirements as $requirement) {
            if ($requirement->isFulfilled()) {
                if ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $rows[] = ['OK', $requirement->getTestMessage()];
                }
            } elseif ($requirement->isOptional()) {
                if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                    $rows[] = ['WARNING', $requirement->getHelpText()];
                }
            } else if ($verbosity >= OutputInterface::VERBOSITY_NORMAL) {
                $rows[] = ['ERROR', $requirement->getHelpText()];
            }
        }

        if (!empty($rows)) {
            $table = new Table($output);
            $table
                ->setHeaders(['Check  ', $header])
                ->setRows([]);
            foreach ($rows as $row) {
                $table->addRow($row);
            }
            $table->render();
        }
    }
}

