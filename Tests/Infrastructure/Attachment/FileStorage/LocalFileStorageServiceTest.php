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
namespace Diamante\DeskBundle\Tests\Infrastructure\Attachment\FileStorage;

use Diamante\DeskBundle\Infrastructure\Attachment\FileStorage\LocalFileStorageService;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Tests\Stubs\FileInfoStub;

class LocalFileStorageServiceTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_REAL_PATH = 'system/app/attachments';
    const DUMMY_FILENAME  = 'dummy-filename.ext';
    const DUMMY_CONTENT   = 'DUMMY_CONTENT';

    /**
     * @var LocalFileStorageService
     */
    private $localFileStorageService;

    /**
     * @var FileInfoStub
     */
    private $fileInfo;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     * @Mock \Symfony\Component\Filesystem\Filesystem
     */
    private $fs;

    protected function setUp()
    {
        MockAnnotations::init($this);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable, doesn't exist or no space left on the disk.
     */
    public function thatThrowsExceptionIfDestinationDirectoryCannotBeCreated()
    {
        $this->fileInfo = $this->getFileInfoInstance(self::DUMMY_REAL_PATH);
        $this->localFileStorageService = $this->getLocalFileStorageServiceInstance($this->fileInfo);

        $this->fs->expects($this->once())->method('mkdir')->with($this->equalTo(self::DUMMY_REAL_PATH))
            ->will($this->throwException(new \Exception()));

        $this->assertEquals(false, $this->fileInfo->isDir());
        $this->assertEquals(false, $this->fileInfo->isWritable());

        $this->localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable, doesn't exist or no space left on the disk.
     */
    public function thatThrowsExceptionIfDestinationDirectoryIsNotWritable()
    {
        $this->fileInfo = $this->getFileInfoInstance(self::DUMMY_REAL_PATH, true, false);
        $this->localFileStorageService = $this->getLocalFileStorageServiceInstance($this->fileInfo);

        $this->assertEquals(true, $this->fileInfo->isDir());
        $this->assertEquals(false, $this->fileInfo->isWritable());
        $this->localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);
    }

    /**
     * @test
     */
    public function thatFileUploads()
    {
        $this->fileInfo = $this->getFileInfoInstance(self::DUMMY_REAL_PATH, true, true);
        $this->localFileStorageService = $this->getLocalFileStorageServiceInstance($this->fileInfo);

        $this->fs->expects($this->once())->method('dumpFile')->with(
            $this->equalTo(
                $this->fileInfo->getPathname() . DIRECTORY_SEPARATOR . self::DUMMY_FILENAME),
                $this->equalTo(self::DUMMY_CONTENT
                )
        );

        $fileRealPath = $this->localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);

        $this->assertEquals($this->fileInfo->getPathname() . DIRECTORY_SEPARATOR . self::DUMMY_FILENAME, $fileRealPath);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage File name can not be empty string.
     */
    public function thatFileRemovingThrowsExceptionWhenFilenameIsEmpty()
    {
        $this->fileInfo = $this->getFileInfoInstance(self::DUMMY_REAL_PATH, true, true);
        $this->localFileStorageService = $this->getLocalFileStorageServiceInstance($this->fileInfo);

        $this->localFileStorageService->remove('');
    }

    /**
     * @test
     */
    public function thatFileRemoves()
    {
        $this->fileInfo = $this->getFileInfoInstance(self::DUMMY_REAL_PATH, true, true);
        $this->localFileStorageService = $this->getLocalFileStorageServiceInstance($this->fileInfo);

        $this->fs->expects($this->once())->method('remove')->with(
            $this->equalTo(self::DUMMY_REAL_PATH . DIRECTORY_SEPARATOR . self::DUMMY_FILENAME)
        );

        $this->localFileStorageService->remove(self::DUMMY_FILENAME);
    }

    private function getFileInfoInstance($path, $isDir = false, $isWritable = false)
    {
        return new FileInfoStub($path, $isDir, $isWritable);
    }

    private function getLocalFileStorageServiceInstance(FileInfoStub $fileInfo)
    {
        return new LocalFileStorageService($fileInfo, $this->fs);
    }
}
