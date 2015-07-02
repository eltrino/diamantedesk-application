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
use Diamante\DeskBundle\Command\FixturesPurgeCommand;

if (!is_file($autoload = realpath(__DIR__ . getenv('CLASS_AUTOLOADER')))) {
    throw new \LogicException('Run "composer install --dev" to create autoloader.');
}

// Set kernel folder path dynamically to avoid absolute path in config file
$_SERVER['KERNEL_DIR'] = realpath(__DIR__ . getenv('KERNEL_DIR'));

$loader = require $autoload;
$output = new ConsoleOutput();

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
