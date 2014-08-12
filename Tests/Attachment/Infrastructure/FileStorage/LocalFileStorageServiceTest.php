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
namespace Eltrino\DiamanteDeskBundle\Tests\Attachment\Infrastructure\FileStorage;

use Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\FileStorage\LocalFileStorageService;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Eltrino\DiamanteDeskBundle\Tests\Stubs\TestFileInfo;

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
     * @var TestFileInfo
     */
    private $fileInfo;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     * @Mock \Symfony\Component\Filesystem\Filesystem
     */
    private $fs;

    /**
     * @var
     */
    private $tempDir;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->fileInfo = new TestFileInfo(self::DUMMY_REAL_PATH);
        $this->localFileStorageService = new LocalFileStorageService($this->fileInfo, $this->fs);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable, doesn't exist or no space left on the disk.
     */
    public function thatThrowsExceptionIfDestinationDirectoryCannotBeCreated()
    {
        $this->fs->expects($this->once())->method('mkdir')->with($this->equalTo(self::DUMMY_REAL_PATH))
            ->will($this->throwException(new \Exception()));

        $this->assertEquals(false, $this->fileInfo->isDir());
        $this->assertEquals(false, $this->fileInfo->isWritable());

        $justUploadedFile = $this->localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable, doesn't exist or no space left on the disk.
     */
    public function thatThrowsExceptionIfDestinationDirectoryIsNotWritable()
    {
        $this->assertEquals(false, $this->fileInfo->isWritable());
        $justUploadedFile = $this->localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);
    }

    /**
     * @test
     */
    public function thatFileUploads()
    {
        $tempDir = sys_get_temp_dir();
        $fileInfo = new TestFileInfo($tempDir);
        $localFileStorageService = new LocalFileStorageService($fileInfo, $this->fs);

        $this->fs->expects($this->once())->method('dumpFile')->with(
            $this->equalTo($fileInfo->getPathname() . '/' . self::DUMMY_FILENAME), $this->equalTo(self::DUMMY_CONTENT)
        );

        $fileRealPath = $localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);

        $this->assertEquals($fileInfo->getPathname() . '/' . self::DUMMY_FILENAME, $fileRealPath);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage File name can not be empty string.
     */
    public function thatFileRemovingThrowsExceptionWhenFilenameIsEmpty()
    {
        $this->localFileStorageService->remove('');
    }

    /**
     * @test
     */
    public function thatFileRemoves()
    {
        $this->fs->expects($this->once())->method('remove')->with(
            $this->equalTo(self::DUMMY_REAL_PATH . '/' . self::DUMMY_FILENAME)
        );

        $this->localFileStorageService->remove(self::DUMMY_FILENAME);
    }
}
