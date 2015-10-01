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

namespace Diamante\UserBundle\Tests\Twig\Extensions;

use Oro\Bundle\TagBundle\Entity\TagManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Diamante\DeskBundle\Twig\Extensions\RenderTagExtension;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Entity\Ticket;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Doctrine\Common\Collections\ArrayCollection;
use \Twig_Environment;

class RenderTagExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RenderTagExtension
     */
    private $renderTagExtensionExtension;

    /**
     * @var TagManager
     * @Mock \Oro\Bundle\TagBundle\Entity\TagManager
     */
    private $tagManager;

    /**
     * @var Registry
     * @Mock \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $registry;

    /**
     * @var Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $sharedRepository;

    /**
     * @var ArrayCollection
     * @Mock \Doctrine\Common\Collections\ArrayCollection
     */
    private $commonCollection;

    /**
     * @var Twig_Environment
     * @Mock \Twig_Environment
     */
    private $twig;

    /**
     * @var Branch
     * @Mock Diamante\DeskBundle\Entity\Branch
     */
    private $branch;

    /**
     * @var Ticket
     * @Mock Diamante\DeskBundle\Entity\Ticket
     */
    private $ticket;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->renderTagExtensionExtension = new RenderTagExtension($this->tagManager, $this->registry);
    }

    public function testGetFunctions()
    {
        $functions = $this->renderTagExtensionExtension->getFunctions();

        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
        }
    }

    public function testWithoutContext()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->renderTagExtensionExtension->renderTag($this->twig, 1, '');
    }
    

    public function testRenderBranch()
    {
        $tags = array(array('id' => 1, 'name' => 'Branch Tag'));
        $renderTagResult = '<span class="tag-inline">Branch Tag</span>';

        $this->registry->expects(($this->once()))
            ->method('getRepository')
            ->will($this->returnValue($this->sharedRepository));

        $this->tagManager->expects(($this->once()))
            ->method('loadTagging')
            ->will($this->returnValue($this->tagManager));

        $this->twig->expects(($this->once()))
            ->method('render')
            ->will($this->returnValue($renderTagResult));

        $this->sharedRepository->expects(($this->once()))
            ->method('get')
            ->will($this->returnValue($this->branch));

        $this->branch->expects($this->once())
            ->method('getTags')
            ->will($this->returnValue($this->commonCollection));

        $this->commonCollection->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($tags));

        $branchResult = $this->renderTagExtensionExtension->renderTag($this->twig, 1, 'branch');

        $this->assertEquals($renderTagResult, $branchResult);
    }

    public function testRenderTicket()
    {
        $tags = array(array('id' => 1, 'name' => 'Ticket Tag'));
        $renderTagResult = '<span class="tag-inline">Ticket Tag</span>';

        $this->registry->expects(($this->once()))
            ->method('getRepository')
            ->will($this->returnValue($this->sharedRepository));

        $this->tagManager->expects(($this->once()))
            ->method('loadTagging')
            ->will($this->returnValue($this->tagManager));

        $this->twig->expects(($this->once()))
            ->method('render')
            ->will($this->returnValue($renderTagResult));

        $this->sharedRepository->expects(($this->once()))
            ->method('get')
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())
            ->method('getTags')
            ->will($this->returnValue($this->commonCollection));

        $this->commonCollection->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($tags));

        $ticketResult = $this->renderTagExtensionExtension->renderTag($this->twig, 1, 'ticket');

        $this->assertEquals($renderTagResult, $ticketResult);
    }
} 