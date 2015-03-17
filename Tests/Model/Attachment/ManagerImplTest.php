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
namespace Diamante\DeskBundle\Tests\Model\Attachment;

use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Model\Attachment\File;
use Diamante\DeskBundle\Model\Attachment\ManagerImpl;
use Diamante\DeskBundle\Tests\Model\Attachment\AttachmentStub;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class ManagerImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerImpl
     */
    private $manager;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\Services\FileStorageService
     * @Mock \Diamante\DeskBundle\Model\Attachment\Services\FileStorageService
     */
    private $fileStorageService;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\AttachmentFactory
     * @Mock \Diamante\DeskBundle\Model\Attachment\AttachmentFactory
     */
    private $factory;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $repository;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\AttachmentHolder
     * @Mock \Diamante\DeskBundle\Model\Attachment\AttachmentHolder
     */
    private $holder;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Attachment\Imagine\Data\Loader\FileSystemAttachmentLoader
     * @Mock Diamante\DeskBundle\Infrastructure\Attachment\Imagine\Data\Loader\FileSystemAttachmentLoader
     */
    private $imagine;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     * @Mock Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->manager = new ManagerImpl(
            $this->fileStorageService,
            $this->factory,
            $this->repository,
            $this->imagine,
            $this->logger
        );
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Given filename is invalid.
     */
    public function createNewAttachmentThrowsExceptionWhenFilenameIsNotValid()
    {
        $filename = 1;
        $this->manager->createNewAttachment($filename, 'content', $this->holder);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Given file content is invalid.
     */
    public function createNewAttachmentThrowsExceptionWhenContentIsNotValid()
    {
        $content = array();
        $this->manager->createNewAttachment('file.ext', $content, $this->holder);
    }

    /**
     * @test
     */
    public function createNewAttachment()
    {
        $filename     = 'file.ext';
        $content      = '_some_dummy_content';
        $fileRealPath = 'dummy/file/real/path/' . $filename;

        $attachmentId = 1;
        $attachment = new AttachmentStub(new File($fileRealPath));
        $attachment->setId($attachmentId);

        $this->fileStorageService->expects($this->once())->method('upload')->with(
            $this->logicalAnd(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->stringContains($filename)), $this->equalTo($content)
            )
            ->will($this->returnValue($fileRealPath));

        $this->factory->expects($this->once())->method('create')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Diamante\DeskBundle\Model\Attachment\File'),
                $this->callback(function ($other) use ($filename) {
                    /**
                     * @var $other \Diamante\DeskBundle\Model\Attachment\File
                     */
                    return $filename == $other->getFilename();
                })
            )
            )->will($this->returnValue($attachment));

        $this->holder->expects($this->once())->method('addAttachment')->with($this->equalTo($attachment));
        $this->repository->expects($this->once())->method('store')->with($this->equalTo($attachment));

        $createdAttachment = $this->manager->createNewAttachment($filename, $content, $this->holder);

        $this->assertNotNull($createdAttachment->getId());
        $this->assertEquals($attachmentId, $createdAttachment->getId());
    }

    /**
     * @test
     */
    public function deleteAttachment()
    {
        $pathname = 'some/path/file.ext';
        $filename = 'file.ext';
        $attachment = new Attachment(new File($pathname));

        $this->fileStorageService->expects($this->once())->method('remove')->with($this->equalTo($filename));
        $this->repository->expects($this->once())->method('remove')->with($this->equalTo($attachment));

        $this->manager->deleteAttachment($attachment);
    }
}
