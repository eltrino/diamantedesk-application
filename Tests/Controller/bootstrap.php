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

require_once __DIR__.'/../../../../../app/bootstrap.php.cache';
require_once __DIR__.'/../../../../../app/AppKernel.php';

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Eltrino\DiamanteDeskBundle\Command\FixturesPurgeCommand;

$kernel = new AppKernel('test', true);
$kernel->boot();

$application = new Application($kernel);
$kernelDir = $kernel->getRootDir();

$autoloadFlag = getenv('AUTOLOAD_FIXTURES');
$output = new ConsoleOutput();

if (!is_file($autoload = $kernelDir . '/../vendor/autoload.php')) {
    throw new \LogicException('Run "composer install --dev" to create autoloader.');
}

$loader = require $autoload;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

// Set kernel folder path dynamically to avoid absolute path in config file
$_SERVER['KERNEL_DIR'] = $kernelDir;

if (true === (bool)$autoloadFlag) {
    $loadCommand = new LoadDataFixturesDoctrineCommand();
    $purgeCommand = new FixturesPurgeCommand();

    $application->add($purgeCommand);
    $purgeInput = new ArrayInput(array(
        'command'              => 'diamante:fixtures:purge',
        '--no-interaction'     => true,
    ));

    $application->add($loadCommand);
    $input = new ArrayInput(array(
        'command'               => 'doctrine:fixtures:load',
        '--fixtures'            => "{$kernelDir}/../src/Eltrino/DiamanteDeskBundle/DataFixtures/Test",
        '--append'              => true,
        '--no-interaction'      => true

    ));

    try {
        $output->writeln('Removing previously loaded test fixtures');
        $purgeCommand->run($purgeInput, $output);
        $output->writeln("Loading fixtures...\n");
        $loadCommand->run($input, $output);
    } catch (\Exception $e) {
        $output->writeln("\n");
        $output->writeln("Failed to load fixtures. Error: " . $e->getMessage());
        $output->writeln("\n");
    }
} else {
    $output->writeln('Autoload of test fixtures with prior purge is disabled in config');
}

return $loader;
