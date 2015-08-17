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
namespace Diamante\DeskBundle\Tests\Infrastructure\Persistence\Doctrine;

use Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\BranchListener;
use Diamante\DeskBundle\Model\Branch\Branch;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class BranchListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     * @Mock \Doctrine\ORM\EntityManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        MockAnnotations::init($this);
    }

    /**
     * @expectedException \Diamante\DeskBundle\Model\Branch\Exception\DuplicateBranchKeyException
     * @expectedExceptionMessage Branch key already exists. Please, provide another one.
     */
    public function testPrePersistHandler()
    {
        $branch = new Branch('DUMM', 'Dummy', 'Desc');
        $event = new LifecycleEventArgs($branch, $this->objectManager);

        $dqlQueryStr = "SELECT b FROM DiamanteDeskBundle:Branch b WHERE b.key = :key";

        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('setParameter', 'getResult'))
            ->getMockForAbstractClass();

        $this->objectManager->expects($this->once())->method('createQuery')->with($dqlQueryStr)
            ->will($this->returnValue($query));
        $query->expects($this->once())->method('setParameter')->with('key', $branch->getKey())
            ->will($this->returnValue($query));
        $query->expects($this->once())->method('getResult')->will($this->returnValue(array($branch)));

        $listener = new BranchListener();
        $listener->prePersistHandler($branch, $event);
    }

    public function testPrePersistHandlerWhenKeyDoesNotExistYet()
    {
        $branch = new Branch('DUMM', 'Dummy', 'Desc');
        $event = new LifecycleEventArgs($branch, $this->objectManager);

        $dqlQueryStr = "SELECT b FROM DiamanteDeskBundle:Branch b WHERE b.key = :key";

        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('setParameter', 'getResult'))
            ->getMockForAbstractClass();

        $this->objectManager->expects($this->once())->method('createQuery')->with($dqlQueryStr)
            ->will($this->returnValue($query));
        $query->expects($this->once())->method('setParameter')->with('key', $branch->getKey())
            ->will($this->returnValue($query));
        $query->expects($this->once())->method('getResult')->will($this->returnValue(array()));

        $listener = new BranchListener();
        $listener->prePersistHandler($branch, $event);
    }
} 
