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
namespace Eltrino\DiamanteDeskBundle\Tests\Branch\Model;

use Eltrino\DiamanteDeskBundle\Branch\Infrastructure\BranchLogoHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Eltrino\DiamanteDeskBundle\Branch\Model\Exception\LogoHandlerLogicException;

class BranchLogoHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SplFileInfo
     * @Mock \SplFileInfo
     */
    private $dirMock;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     * @Mock \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $fileMock;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     * @Mock \Symfony\Component\Filesystem\Filesystem
     */
    private $fileSysMock;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Infrastructure\BranchLogoHandler
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Infrastructure\BranchLogoHandler
     */
    private $handler;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->handler = new BranchLogoHandler($this->dirMock, $this->fileSysMock);
    }

    /**
     * @test
     * @expectedException \Eltrino\DiamanteDeskBundle\Branch\Model\Exception\LogoHandlerLogicException
     */
    public function thatFileUploadThrowExceptionWhenMimeTypeIsNotPermitted()
    {
        $this->fileMock
            ->expects($this->exactly(2))
            ->method('getMimeType')
            ->will($this->returnValue('wrongMimeType'));

        $this->handler->upload($this->fileMock);
    }

    /**
     * @test
     */
    public function thatFileUploadToCorrectDir()
    {
        $this->fileMock
            ->expects($this->exactly(1))
            ->method('getMimeType')
            ->will($this->returnValue('image/gif'));

        $this->fileMock
            ->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('dummy'));

        $this->dirMock->expects($this->exactly(1))
            ->method('getRealPath')
            ->will($this->returnValue('logo/fdirectory/full/path'));

        $this->dirMock->expects($this->once())
            ->method('isDir')
            ->will($this->returnValue(true));

        $this->dirMock->expects($this->once())
            ->method('isWritable')
            ->will($this->returnValue(true));

        $movedFile = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileMock->expects($this->once())
            ->method('move')
            ->with($this->equalTo('logo/fdirectory/full/path'), $this->matchesRegularExpression('/.(\.)dummy/'))
            ->will($this->returnValue($movedFile));

        $this->handler->upload($this->fileMock);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function thatFileUploadThrowExceptionWhenDirectoryIsNotWritable()
    {
        $this->fileMock
            ->expects($this->exactly(1))
            ->method('getMimeType')
            ->will($this->returnValue('image/gif'));

        $this->fileMock
            ->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('dummy'));

        $this->dirMock->expects($this->once())
            ->method('isDir')
            ->will($this->returnValue(true));

        $this->dirMock->expects($this->once())
            ->method('isWritable')
            ->will($this->returnValue(false));

        $this->handler->upload($this->fileMock);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function thatFileUploadThrowExceptionWhenDirectoryDoesNotExist()
    {
        $this->fileMock
            ->expects($this->exactly(1))
            ->method('getMimeType')
            ->will($this->returnValue('image/gif'));

        $this->fileMock
            ->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('dummy'));

        $this->dirMock->expects($this->once())
            ->method('isDir')
            ->will($this->returnValue(false));

        $this->dirMock->expects($this->exactly(0))
            ->method('isWritable')
            ->will($this->returnValue(false));

        $this->handler->upload($this->fileMock);
    }
}
