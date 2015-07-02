<?php

namespace Diamante\FrontBundle\Utility;

use Symfony\Component\Process\Process;

class DependencyLocator
{
    const PROBE_VERSION_LONG  = '--version';
    const PROBE_VERSION_SHORT = '-v';

    protected $paths = [
        '/usr/local/bin',
        '/usr/bin',
        '/usr/sbin',
        '/opt/local/bin',
    ];

    protected $resolved = false;

    public function locate($dependency, $probe = self::PROBE_VERSION_LONG)
    {
        $possibleLocation = null;

        foreach ($this->paths as $path) {
            $possibleLocation = sprintf('%s/%s', $path, $dependency);

            if (!file_exists($possibleLocation) || !is_executable($possibleLocation)) {
                continue;
            }

            $this->ensureDependencyOperational($possibleLocation, $probe));

            if (!$this->resolved) {
                $possibleLocation = null;
            }

        }

        return $possibleLocation;
    }

    protected function ensureDependencyOperational($object, $probe = self::PROBE_VERSION_LONG)
    {
        $command = sprintf("%s %s", $object, $probe);

        $process = new Process($command);

        if ($process->run() > 0) {
            $this->resolved = false;
        }

        $this->resolved = true;
    }
}