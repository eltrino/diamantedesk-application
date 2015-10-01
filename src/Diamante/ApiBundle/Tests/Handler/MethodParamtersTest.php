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

namespace Diamante\ApiBundle\Tests\Handler;

use Diamante\ApiBundle\Handler\MethodParameters;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class MethodParametersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Validator\Validator\RecursiveValidator
     * @Mock \Symfony\Component\Validator\Validator\RecursiveValidator
     */
    private $validator;

    protected function setUp()
    {
        MockAnnotations::init($this);
    }

    public function testPutIn()
    {
        $reflection = new \ReflectionMethod('Diamante\ApiBundle\Tests\Handler\Fixtures\Object', 'getParts');

        $dummyBagFirst = new ParameterBag([
            'id' => '1'
        ]);

        $dummyBagSecond = new ParameterBag([
            'anotherId' => '56',
            'sub_map' => '1'
        ]);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue([]));

        $methodParameters = new MethodParameters($reflection, $this->validator);
        $methodParameters->addParameterBag($dummyBagFirst);
        $methodParameters->addParameterBag($dummyBagSecond);
        $methodParameters->putIn($dummyBagFirst);

        $this->assertEquals($dummyBagSecond->get('anotherId'), $dummyBagFirst->get('anotherId'));
        $this->assertTrue($dummyBagFirst->has('map'));
        $this->assertTrue($dummyBagFirst->has('subMap'));
        $this->assertEquals(4, $dummyBagFirst->count());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not all properties set correctly
     */
    public function thatPutInThrowsValidatorException()
    {
        $reflection = new \ReflectionMethod('Diamante\ApiBundle\Tests\Handler\Fixtures\Object', 'getParts');

        $dummyBagFirst = new ParameterBag([
            'id' => '1'
        ]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList([new ConstraintViolation(
                'Not all properties set correctly', 'Not all properties set correctly', [], '', '', ''
            )])));

        $methodParameters = new MethodParameters($reflection, $this->validator);
        $methodParameters->putIn($dummyBagFirst);
    }
}
