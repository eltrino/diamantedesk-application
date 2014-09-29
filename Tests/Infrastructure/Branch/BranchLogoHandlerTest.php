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
namespace Diamante\DeskBundle\Tests\Infrastructure\Branch;

use Diamante\DeskBundle\Infrastructure\Branch\BranchLogoHandler;
use Diamante\DeskBundle\Model\Branch\Branch;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Model\Branch\Exception\LogoHandlerLogicException;
use Diamante\DeskBundle\Tests\Stubs\FileInfoStub;
use Diamante\DeskBundle\Tests\Stubs\UploadedFileStub;

class BranchLogoHandlerTest extends \PHPUnit_Framework_TestCase
{

    const PNG_FIXTURE_NAME = 'fixture.png';
    const BMP_FIXTURE_NAME = 'fixture.bmp';
    const NON_WRITABLE_DIR = '/var/log';
    const NON_EXISTENT_DIR = '/non_existent_dir';
    const FIXTURE_FOLDER   = '/../../Fixture/files';

    /**
     * @var FileInfoStub
     */
    private $dirMock;

    /**
     * @var UploadedFileStub
     */
    private $fileMock;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     * @Mock \Symfony\Component\Filesystem\Filesystem
     */
    private $fileSysMock;

    /**
     * @var BranchLogoHandler
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
        $this->dirMock = new FileInfoStub(self::NON_WRITABLE_DIR, false, false);
        $this->fixturesDir = __DIR__ . self::FIXTURE_FOLDER;
        $this->fileMock = new UploadedFileStub(
            $this->fixturesDir . '/' . self::PNG_FIXTURE_NAME,
            self::PNG_FIXTURE_NAME
        );
        $this->handler = new BranchLogoHandler($this->dirMock, $this->fileSysMock);
    }

    /**
     * @test
     * @expectedException \Diamante\DeskBundle\Model\Branch\Exception\LogoHandlerLogicException
     */
    public function thatFileUploadThrowExceptionWhenMimeTypeIsNotPermitted()
    {
        $pictureWithNotPermittedMimeType = new UploadedFileStub(
            $this->fixturesDir . '/' . self::BMP_FIXTURE_NAME,
            self::BMP_FIXTURE_NAME
        );
        $this->handler->upload($pictureWithNotPermittedMimeType, self::BMP_FIXTURE_NAME);
    }

    /**
     * @test
     */
    public function thatFileUploadToCorrectDir()
    {
        $this->dirMock = new FileInfoStub(self::NON_WRITABLE_DIR, true, true);
        $this->handler = new BranchLogoHandler($this->dirMock, $this->fileSysMock);

        $this->fileMock = new UploadedFileStub(
            $this->fixturesDir . '/' . self::PNG_FIXTURE_NAME,
            self::PNG_FIXTURE_NAME, 'image/png'
        );
        $this->assertEquals('image/png', $this->fileMock->getMimeType());
        $this->assertEquals('png', strtolower($this->fileMock->guessExtension()));

        $this->assertTrue($this->dirMock->isDir());
        $this->assertTrue($this->dirMock->isWritable());

        $uploadedFile = $this->handler->upload($this->fileMock, self::PNG_FIXTURE_NAME);
        $this->assertEquals(
            self::NON_WRITABLE_DIR . '/' . self::PNG_FIXTURE_NAME,
            $uploadedFile->getPathname()
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function thatFileUploadThrowExceptionWhenDirectoryIsNotWritable()
    {
        $this->dirMock = new FileInfoStub(self::NON_WRITABLE_DIR, true, false);
        $this->handler = new BranchLogoHandler($this->dirMock, $this->fileSysMock);
        $this->fileMock = new UploadedFileStub(
            $this->fixturesDir . '/' . self::PNG_FIXTURE_NAME,
            self::PNG_FIXTURE_NAME, 'image/png'
        );


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
        $this->dirMock = new FileInfoStub(self::NON_EXISTENT_DIR, false, false);
        $this->handler = new BranchLogoHandler($this->dirMock, $this->fileSysMock);
        $this->fileMock = new UploadedFileStub(
            $this->fixturesDir . '/' . self::PNG_FIXTURE_NAME,
            self::PNG_FIXTURE_NAME, 'image/png'
        );

        $this->assertEquals('image/png', $this->fileMock->getMimeType());
        $this->assertEquals('png', strtolower($this->fileMock->guessExtension()));

        $this->assertEquals(false, $this->dirMock->isDir());
        $this->assertEquals(false, $this->dirMock->isWritable());

        $this->handler->upload($this->fileMock, self::PNG_FIXTURE_NAME);
    }
}
