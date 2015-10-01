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
