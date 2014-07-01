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

namespace Eltrino\DiamanteDeskBundle\Tests\Attachment\Api;

use Eltrino\DiamanteDeskBundle\Attachment\Api\AttachmentServiceImpl;
use Eltrino\DiamanteDeskBundle\Entity\Attachment;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class AttachmentServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_FILENAME      = 'dummy_file.jpg';
    const DUMMY_ATTACHMENT_ID = 1;

    /**
     * @var AttachmentServiceImpl
     */
    private $service;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentFactory
     * @Mock \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentRepository
     */
    private $attachmentRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\FileUpload\FileUploadHandler
     * @Mock \Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\FileUpload\FileUploadHandler
     */
    private $fileUploadHandler;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\File\FileRemoveHandler
     * @Mock \Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\File\FileRemoveHandler
     */
    private $fileRemoveHandler;

    /**
     * @var \Symfony\Component\HttpFoundation\File\File
     * @Mock \Symfony\Component\HttpFoundation\File\File
     */
    private $file;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\Attachment
     * @Mock \Eltrino\DiamanteDeskBundle\Entity\Attachment
     */
    private $attachment;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentHolder
     * @Mock \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentHolder
     */
    private $attachmentHolder;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     * @Mock \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $uploadedFile;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->service = new AttachmentServiceImpl(
            $this->attachmentFactory, $this->attachmentRepository, $this->fileUploadHandler, $this->fileRemoveHandler
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function thatAttachmentCreationThrowsException()
    {
        $this->fileUploadHandler->expects($this->once())->method('upload')->with($this->equalTo($this->uploadedFile))
            ->will($this->throwException(new \RuntimeException()));

        $this->service
            ->createAttachment($this->uploadedFile, $this->attachmentHolder);
    }

    /**
     * @test
     */
    public function thatAttachmentCreates()
    {
        $this->file->expects($this->once())->method('getFilename')->will($this->returnValue(self::DUMMY_FILENAME));

        $this->fileUploadHandler->expects($this->once())->method('upload')->with($this->equalTo($this->uploadedFile))
            ->will($this->returnValue($this->file));

        $this->attachmentFactory->expects($this->once())->method('create')->with($this->equalTo(self::DUMMY_FILENAME))
            ->will($this->returnValue($this->attachment));

        $this->attachment->expects($this->once())->method('getId')->will($this->returnValue(self::DUMMY_ATTACHMENT_ID));

        $this->attachmentRepository->expects($this->once())->method('store')->with($this->equalTo($this->attachment));

        $attachmentId = $this->service
            ->createAttachment($this->uploadedFile, $this->attachmentHolder);

        $this->assertEquals(self::DUMMY_ATTACHMENT_ID, $attachmentId);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment not found.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenAttachmentDoesNotExist()
    {
        $this->attachmentRepository->expects($this->once())->method('get')
            ->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue(null));

        $this->service->removeAttachment(self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can not remove attachment.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenRemovesFile()
    {
        $this->attachmentRepository->expects($this->once())->method('get')
            ->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($this->attachment));

        $this->fileRemoveHandler->expects($this->once())->method('remove')->with($this->equalTo(''))
            ->will($this->throwException(new \Exception()));

        $this->service->removeAttachment(self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     */
    public function thatAttachmentRemoves()
    {
        $attachment = new Attachment(self::DUMMY_FILENAME);

        $this->attachmentRepository->expects($this->once())->method('get')
            ->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($attachment));

        $this->fileRemoveHandler->expects($this->once())->method('remove')->with($this->equalTo(self::DUMMY_FILENAME));

        $this->attachmentRepository->expects($this->once())->method('remove')->with($this->equalTo($attachment));

        $this->service->removeAttachment(self::DUMMY_ATTACHMENT_ID);
    }
}
