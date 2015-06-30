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

    public function locate($dependency, $probe = self::PROBE_VERSION_LONG)
    {
        $possibleLocation = null;

        foreach ($this->paths as $path) {
            $possibleLocation = sprintf('%s/%s', $path, $dependency);

            if (!file_exists($possibleLocation) || !is_executable($possibleLocation)) {
                continue;
            }

            if (!$this->ensureDependencyOperational($possibleLocation, $probe)) {
                continue;
            }
        }

        return $possibleLocation;
    }

    protected function ensureDependencyOperational($object, $probe = self::PROBE_VERSION_LONG)
    {
        $command = sprintf("%s %s", $object, $probe);

        $process = new Process($command);

        if ($process->run() > 0) {
            return false;
        }

        return true;
    }
}