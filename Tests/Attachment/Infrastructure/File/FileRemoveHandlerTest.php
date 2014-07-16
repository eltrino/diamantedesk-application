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

namespace Eltrino\DiamanteDeskBundle\Tests\Attachment\Infrastructure\File;

use Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\File\FileRemoveHandler;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class FileRemoveHandlerTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_REAL_PATH = '/system/app/attachments';
    const DUMMY_FILENAME  = 'filename.ext';

    /**
     * @var FileRemoveHandler
     */
    private $fileRemoveHandler;

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
        $this->fileRemoveHandler = new FileRemoveHandler($this->fs, $this->fileInfo);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Attachment validation failed, filename cannot be empty.
     */
    public function thatFileRemovingThrowsExceptionWhenFilenameIsEmpty()
    {
        $this->fileRemoveHandler->remove('');
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

        $this->fileRemoveHandler->remove(self::DUMMY_FILENAME);
    }
}
