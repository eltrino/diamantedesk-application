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

class LocalFileStorageServiceTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_REAL_PATH = '/system/app/attachments';
    const DUMMY_FILENAME  = 'dummy-filename.ext';
    const DUMMY_CONTENT   = 'DUMMY_CONTENT';

    /**
     * @var LocalFileStorageService
     */
    private $localFileStorageService;

    /**
     * @var \SplFileInfo
     * @Mock \SplFileInfo
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
        $this->localFileStorageService = new LocalFileStorageService($this->fileInfo, $this->fs);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable, doesn't exist or no space left on the disk.
     */
    public function thatThrowsExceptionIfDestinationDirectoryCannotBeCreated()
    {
        $this->fileInfo->expects($this->once())->method('isDir')->will($this->returnValue(false));
        $this->fileInfo->expects($this->once())->method('getPathname')->will($this->returnValue(self::DUMMY_REAL_PATH));
        $this->fs->expects($this->once())->method('mkdir')->with($this->equalTo(self::DUMMY_REAL_PATH))
            ->will($this->throwException(new \Exception()));

        $justUploadedFile = $this->localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable, doesn't exist or no space left on the disk.
     */
    public function thatThrowsExceptionIfDestinationDirectoryIsNotWritable()
    {
        $this->fileInfo->expects($this->once())->method('isDir')->will($this->returnValue(true));
        $this->fileInfo->expects($this->once())->method('isWritable')->will($this->returnValue(false));

        $justUploadedFile = $this->localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);
    }

    /**
     * @test
     */
    public function thatFileUploads()
    {
        $this->fileInfo->expects($this->once())->method('isDir')->will($this->returnValue(true));

        $this->fileInfo->expects($this->once())->method('isWritable')->will($this->returnValue(true));

        $this->fileInfo->expects($this->exactly(2))->method('getRealPath')->will($this->returnValue(self::DUMMY_REAL_PATH));

        $this->fs->expects($this->once())->method('dumpFile')->with(
            $this->equalTo(self::DUMMY_REAL_PATH . '/' . self::DUMMY_FILENAME), $this->equalTo(self::DUMMY_CONTENT)
        );

        $fileRealPath = $this->localFileStorageService->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);

        $this->assertEquals(self::DUMMY_REAL_PATH . '/' . self::DUMMY_FILENAME, $fileRealPath);
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
        $this->fileInfo->expects($this->once())->method('getRealPath')->will($this->returnValue(self::DUMMY_REAL_PATH));
        $this->fs->expects($this->once())->method('remove')->with(
            $this->equalTo(self::DUMMY_REAL_PATH . '/' . self::DUMMY_FILENAME)
        );

        $this->localFileStorageService->remove(self::DUMMY_FILENAME);
    }
}
