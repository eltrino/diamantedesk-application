<?php

require_once __DIR__ . '/OroRequirements.php';

use Symfony\Component\Process\ProcessBuilder;
use Oro\Bundle\InstallerBundle\Process\PhpExecutableFinder;

/**
 * This class specifies all requirements that are necessary to run the DiamanteDesk Application.
 */
class DiamanteDeskRequirements extends OroRequirements
{

    public function __construct()
    {
        parent::__construct();

        $baseDir = realpath(__DIR__ . '/..');

        $this->addDiamanteDeskRequirement(
            is_writable($baseDir . '/var/attachments'),
            'var/attachments/ directory must be writable',
            'Change the permissions of the "<strong>var/attachments/</strong>" directory so that the web server can write into it.'
        );
    }

    /**
     * Adds an Oro specific requirement.
     *
     * @param Boolean     $fulfilled Whether the requirement is fulfilled
     * @param string      $testMessage The message for testing the requirement
     * @param string      $helpHtml The help text formatted in HTML for resolving the problem
     * @param string|null $helpText The help text (when null, it will be inferred from $helpHtml, i.e. stripped from HTML tags)
     */
    public function addDiamanteDeskRequirement($fulfilled, $testMessage, $helpHtml, $helpText = null)
    {
        $this->add(new DiamanteDeskRequirement($fulfilled, $testMessage, $helpHtml, $helpText, false));
    }

    /**
     * Get the list of Oro specific requirements
     *
     * @return array
     */
    public function getDiamanteDeskRequirements()
    {
        return array_filter(
            $this->getRequirements(),
            function ($requirement) {
                return $requirement instanceof DiamanteDeskRequirement;
            }
        );
    }

    /**
     * @return null|string
     */
    protected function checkCliRequirements()
    {
        $finder = new PhpExecutableFinder();
        $command = sprintf(
            '%s %scheck.php',
            $finder->find(),
            __DIR__ . DIRECTORY_SEPARATOR
        );

        exec($command, $output, $exitCode);

        if ($exitCode) {
            return $this->getErrorMessage($output);
        }

        return $exitCode;
    }

    private function getErrorMessage($output)
    {
        $message = '';
        foreach ($output as $line) {
            preg_match('/^warning|error.*/is', $line, $matches);
            if(!empty($matches)) {
                $message .= $matches[0] . PHP_EOL;
            }
        }
        return $message;
    }
}

class DiamanteDeskRequirement extends Requirement
{
}
