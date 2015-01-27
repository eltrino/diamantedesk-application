<?php
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
        new DiamanteEmbeddedFormType();
    }

    /**
     * @test
     */
    public function shouldBuildForm()
    {
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
        $formType = new DiamanteEmbeddedFormType();

        $this->assertEquals('diamante_embedded_form', $formType->getName());
    }
}
