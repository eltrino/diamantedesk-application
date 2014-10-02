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

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Eltrino\DiamanteDeskBundle\Command\FixturesPurgeCommand;

if (!is_file($autoload = realpath(__DIR__ . getenv('CLASS_AUTOLOADER')))) {
    throw new \LogicException('Run "composer install --dev" to create autoloader.');
}

// Set kernel folder path dynamically to avoid absolute path in config file
$_SERVER['KERNEL_DIR'] = realpath(__DIR__ . getenv('KERNEL_DIR'));

$loader = require $autoload;
$output = new ConsoleOutput();

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$autoloadFlag = getenv('AUTOLOAD_FIXTURES');

if (true === (bool)$autoloadFlag) {
    $kernelDir = $_SERVER['KERNEL_DIR'];
    $appKernelClass = $kernelDir . DIRECTORY_SEPARATOR . 'AppKernel.php';

    require $appKernelClass;

    $kernel = new AppKernel('test', true);
    $kernel->boot();

    $application = new Application($kernel);
    $kernelDir = $kernel->getRootDir();


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
        $output->writeln("\033[32m\033[1mRemoving previously loaded test fixtures\033[0m");
        $purgeCommand->run($purgeInput, $output);
        $output->writeln("Loading fixtures...\n");
        $loadCommand->run($input, $output);
    } catch (\Exception $e) {
        $output->writeln("\n");
        $output->writeln("Failed to load fixtures. Error: " . $e->getMessage());
        $output->writeln("\n");
    }
} else {
    $output->writeln("\033[31m\033[1mAutoload of test fixtures with prior purge is disabled in config\033[0m");
}

return $loader;
