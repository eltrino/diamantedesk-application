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
namespace Diamante\DeskBundle\Tests\Model\Branch;

use Diamante\DeskBundle\Model\Branch\DefaultBranchKeyGenerator;

class DefaultBranchKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can not generate key from given name. Name should have at list 2 letters.
     */
    public function testValidatingOfGivenNameForGenerate($branchName)
    {
        $generator = new DefaultBranchKeyGenerator();
        $generator->generate($branchName);
    }

    /**
     * @dataProvider validDataProvider
     * @param string $branchName
     * @param string $key
     */
    public function testGenerate($branchName, $key)
    {
        $generator = new DefaultBranchKeyGenerator();
        $generatedKey = $generator->generate($branchName);

        $this->assertEquals($key, $generatedKey);
    }

    public function invalidDataProvider()
    {
        return array(
            array('D__--'),
            array('d    ')

        );
    }

    public function validDataProvider()
    {
        return array(
            array('Dummy branch', 'DB'),
            array('Du_mmy', 'DUMM'),
            array('task', 'TASK'),
            array('Diamante', 'DIAM'),
            array('Du_mmy branch', 'DB'),
            array('Bq', 'BQ'),
            array('d__iam', 'DIAM'),
            array('b__--- q', 'BQ')
        );
    }
}
