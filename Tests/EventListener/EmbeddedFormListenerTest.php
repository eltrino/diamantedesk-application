<?php

namespace Diamante\EmbeddedFormBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;

use Diamante\EmbeddedFormBundle\EventListener\EmbeddedFormListener;

class EmbeddedFormListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Request */
    protected $request;

    protected function setUp()
    {
        $this->request = new Request([], [], ['_route' => 'oro_embedded_form_']);
    }

    public function testAddDataBranchField()
    {
        $env = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $newField = "<input>";

        $env->expects($this->once())
            ->method('render')
            ->will($this->returnValue($newField));

        $formView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $currentFormData = 'someHTML';
        $formData = [
            'dataBlocks' => [
                [
                    'subblocks' => [
                        ['data' => [$currentFormData]]
                    ]
                ]
            ]
        ];

        $event = $this->getMockBuilder('Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getTwigEnvironment')
            ->will($this->returnValue($env));
        $event->expects($this->once())
            ->method('getFormData')
            ->will($this->returnValue($formData));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formView));

        array_splice($formData['dataBlocks'][0]['subblocks'][0]['data'], 1, 0, $newField);
        $event->expects($this->once())
            ->method('setFormData')
            ->with($formData);

        $listener = new EmbeddedFormListener();
        $listener->setRequest($this->request);
        $listener->addBranchField($event);
    }

    public function testAddDataBranchFieldNoRequest()
    {
        $listener = new EmbeddedFormListener();
        $event = $this->getMockBuilder('Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->never())
            ->method($this->anything());
        $listener->addBranchField($event);
    }
}
