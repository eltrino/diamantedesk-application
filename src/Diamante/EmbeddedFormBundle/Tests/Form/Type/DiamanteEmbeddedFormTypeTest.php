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
namespace Diamante\EmbeddedFormBundle\Tests\Form\Type;

use Diamante\DeskBundle\Form\DataTransformer\AttachmentTransformer;
use Diamante\EmbeddedFormBundle\Form\Type\DiamanteEmbeddedFormType;
use Symfony\Component\Form\FormBuilderInterface;

class DiamanteEmbeddedFormTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeConstructed()
    {
        $this->markTestSkipped("Bundle should be rewritten and tests changed according to those changes");
        new DiamanteEmbeddedFormType();
    }

    /**
     * @test
     */
    public function shouldBuildForm()
    {
        $this->markTestSkipped("Bundle should be rewritten and tests changed according to those changes");
        /** @var \PHPUnit_Framework_MockObject_MockObject | FormBuilderInterface $builder */
        $builder = $this->getMock('\Symfony\Component\Form\FormBuilder', [], [], '', false);

        $builder->expects($this->at(0))
            ->method('add')
            ->with('firstName', 'text')
            ->will($this->returnSelf());

        $builder->expects($this->at(1))
            ->method('add')
            ->with('lastName', 'text')
            ->will($this->returnSelf());

        $builder->expects($this->at(2))
            ->method('add')
            ->with('emailAddress', 'email')
            ->will($this->returnSelf());

        $builder->expects($this->at(3))
            ->method('add')
            ->with('subject', 'text')
            ->will($this->returnSelf());

        $builder->expects($this->at(4))
            ->method('add')
            ->with('description', 'textarea')
            ->will($this->returnSelf());

        $builder->expects($this->at(5))
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->once())
            ->method('create')
            ->with('attachmentsInput', 'file')
            ->will($this->returnSelf());

        $builder->expects($this->once())
            ->method('addModelTransformer')
            ->with($this->equalTo(new AttachmentTransformer()))
            ->will($this->returnSelf());

        $formType = new DiamanteEmbeddedFormType();
        $formType->buildForm($builder, []);
    }

    /**
     * @test
     */
    public function shouldReturnFormName()
    {
        $this->markTestSkipped("Bundle should be rewritten and tests changed according to those changes");
        $formType = new DiamanteEmbeddedFormType();
        $this->assertEquals('diamante_embedded_form', $formType->getName());
    }
}
