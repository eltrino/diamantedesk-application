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
use Eltrino\DiamanteDeskBundle\Branch\Model\Branch;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Eltrino\DiamanteDeskBundle\Branch\Model\Exception\LogoHandlerLogicException;
use Eltrino\DiamanteDeskBundle\Tests\Stubs\TestFileInfo;
use Eltrino\DiamanteDeskBundle\Tests\Stubs\TestUploadedFile;

class BranchLogoHandlerTest extends \PHPUnit_Framework_TestCase
{

    const PNG_FIXTURE_NAME = 'fixture.png';
    const BMP_FIXTURE_NAME = 'fixture.bmp';
    const NON_WRITABLE_DIR = '/var/log';
    const NON_EXISTENT_DIR = '/non_existent_dir';
    const FIXTURE_FOLDER   = '/../../Fixture/files';

    /**
     * @var TestFileInfo
     */
    private $dirMock;

    /**
     * @var TestUploadedFile
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

    /**
     * @var string
     */
    private $uploadDir;

    /**
     * @var string
     */
    private $fixturesDir;


    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->uploadDir = sys_get_temp_dir();
        $this->dirMock = new TestFileInfo($this->uploadDir);
        $this->fixturesDir = __DIR__ . self::FIXTURE_FOLDER;
        $this->fileMock = new TestUploadedFile(
            $this->fixturesDir . DIRECTORY_SEPARATOR . self::PNG_FIXTURE_NAME,
            self::PNG_FIXTURE_NAME
        );
        $this->handler = new BranchLogoHandler($this->dirMock, $this->fileSysMock);
    }

    /**
     * @test
     * @expectedException \Eltrino\DiamanteDeskBundle\Branch\Model\Exception\LogoHandlerLogicException
     */
    public function thatFileUploadThrowExceptionWhenMimeTypeIsNotPermitted()
    {
        $pictureWithNotPermittedMimeType = new TestUploadedFile(
            $this->fixturesDir . DIRECTORY_SEPARATOR . self::BMP_FIXTURE_NAME,
            self::BMP_FIXTURE_NAME
        );
        $this->handler->upload($pictureWithNotPermittedMimeType, self::BMP_FIXTURE_NAME);
    }

    /**
     * @test
     */
    public function thatFileUploadToCorrectDir()
    {
        $this->assertEquals('image/png', $this->fileMock->getMimeType());
        $this->assertEquals('png', strtolower($this->fileMock->guessExtension()));

        $this->assertEquals($this->uploadDir, $this->dirMock->getRealPath());
        $this->assertTrue($this->dirMock->isDir());
        $this->assertTrue($this->dirMock->isWritable());

        $uploadedFile = $this->handler->upload($this->fileMock, self::PNG_FIXTURE_NAME);
        $this->assertEquals(
            $this->uploadDir . DIRECTORY_SEPARATOR . self::PNG_FIXTURE_NAME,
            $uploadedFile->getPathname()
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function thatFileUploadThrowExceptionWhenDirectoryIsNotWritable()
    {
        $this->dirMock = new TestFileInfo(self::NON_WRITABLE_DIR);
        $this->handler = new BranchLogoHandler($this->dirMock, $this->fileSysMock);
        $this->assertEquals('image/png', $this->fileMock->getMimeType());
        $this->assertEquals('png', strtolower($this->fileMock->guessExtension()));

        $this->assertEquals(true, $this->dirMock->isDir());
        $this->assertEquals(false, $this->dirMock->isWritable());

        $this->handler->upload($this->fileMock, self::PNG_FIXTURE_NAME);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function thatFileUploadThrowExceptionWhenDirectoryDoesNotExist()
    {
        $this->dirMock = new TestFileInfo(self::NON_EXISTENT_DIR);
        $this->handler = new BranchLogoHandler($this->dirMock, $this->fileSysMock);
        $this->assertEquals('image/png', $this->fileMock->getMimeType());
        $this->assertEquals('png', strtolower($this->fileMock->guessExtension()));

        $this->assertEquals(false, $this->dirMock->isDir());
        $this->assertEquals(false, $this->dirMock->isWritable());

        $this->handler->upload($this->fileMock, self::PNG_FIXTURE_NAME);
    }
}
