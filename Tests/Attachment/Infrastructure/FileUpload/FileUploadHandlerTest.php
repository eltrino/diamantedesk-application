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

namespace Eltrino\DiamanteDeskBundle\Tests\Attachment\Infrastructure\FileUpload;

use Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\FileUpload\FileUploadHandler;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Symfony\Component\HttpFoundation\File\File;

class FileUploadHandlerTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_REAL_PATH = 'dummy_real_path';
    const DUMMY_FILENAME  = 'dummy-filename.ext';
    const DUMMY_CONTENT   = 'DUMMY_CONTENT';

    /**
     * @var FileUploadHandler
     */
    private $fileUploadHandler;

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
        $this->fileUploadHandler = new FileUploadHandler($this->fileInfo, $this->fs);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable, doesn't exist or no space left on the disk.
     */
    public function thatFileUploadingThrowsExceptionWhenDirIsNotCreated()
    {
        $this->fileInfo->expects($this->once())->method('isDir')->will($this->returnValue(false));
        $this->fileInfo->expects($this->exactly(0))->method('isWritable')->will($this->returnValue(true));

        $justUploadedFile = $this->fileUploadHandler->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable, doesn't exist or no space left on the disk.
     */
    public function thatFileUploadingThrowsExceptionWhenDirIsNotWritable()
    {
        $this->fileInfo->expects($this->once())->method('isDir')->will($this->returnValue(true));
        $this->fileInfo->expects($this->once())->method('isWritable')->will($this->returnValue(false));

        $justUploadedFile = $this->fileUploadHandler->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);
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

        $file = $this->fileUploadHandler->upload(self::DUMMY_FILENAME, self::DUMMY_CONTENT);

        $this->assertInstanceOf('\Eltrino\DiamanteDeskBundle\Attachment\Model\File', $file);
        $this->assertEquals(self::DUMMY_REAL_PATH . '/' . self::DUMMY_FILENAME, $file->getPathname());
        $this->assertEquals(self::DUMMY_FILENAME, $file->getFilename());
    }
}
