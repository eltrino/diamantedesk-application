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
namespace Diamante\DeskBundle\Tests\Attachment\Api;

use Diamante\DeskBundle\Api\Internal\AttachmentServiceImpl;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Entity\Attachment;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Api\Command\CreateAttachmentsCommand;

class AttachmentServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_FILENAME      = 'dummy_file.jpg';
    const DUMMY_FILE_CONTENT  = 'DUMMY_CONTENT';
    const DUMMY_ATTACHMENT_ID = 1;

    /**
     * @var AttachmentServiceImpl
     */
    private $service;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\AttachmentFactory
     * @Mock \Diamante\DeskBundle\Model\Attachment\AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $attachmentRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\Services\FileStorageService
     * @Mock \Diamante\DeskBundle\Model\Attachment\Services\FileStorageService
     */
    private $fileStorageService;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\File
     * @Mock \Diamante\DeskBundle\Model\Attachment\File
     */
    private $file;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\Attachment
     * @Mock \Diamante\DeskBundle\Model\Attachment\Attachment
     */
    private $attachment;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\AttachmentHolder
     * @Mock \Diamante\DeskBundle\Model\Attachment\AttachmentHolder
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
            $this->attachmentFactory, $this->attachmentRepository, $this->fileStorageService
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function thatAttachmentCreationThrowsException()
    {
        $this->fileStorageService->expects($this->once())->method('upload')->with(
                $this->logicalAnd(
                    $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                    $this->stringContains(self::DUMMY_FILENAME)
                ), $this->equalTo(self::DUMMY_FILE_CONTENT)
            )->will($this->throwException(new \RuntimeException()));

        $createAttachmentsCommand = new CreateAttachmentsCommand();
        $createAttachmentsCommand->attachments      = $this->attachmentsInputDTOs();
        $createAttachmentsCommand->attachmentHolder = $this->attachmentHolder;

        $this->service
            ->createAttachments($createAttachmentsCommand);
    }

    /**
     * @test
     */
    public function thatAttachmentCreates()
    {
        $fileRealPath = 'dummy/file/real/path/' . self::DUMMY_FILENAME;
        $this->fileStorageService->expects($this->once())->method('upload')->with(
            $this->logicalAnd(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->stringContains(self::DUMMY_FILENAME)
            ), $this->equalTo(self::DUMMY_FILE_CONTENT)
        )->will($this->returnValue($fileRealPath));

        $this->attachmentFactory->expects($this->once())->method('create')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Diamante\DeskBundle\Model\Attachment\File'),
                $this->callback(function($other) {
                    return AttachmentServiceImplTest::DUMMY_FILENAME == $other->getFilename();
                })
            )
        )->will($this->returnValue($this->attachment));

        $this->attachmentHolder->expects($this->once())->method('addAttachment')->with($this->equalTo($this->attachment));
        $this->attachmentRepository->expects($this->once())->method('store')->with($this->equalTo($this->attachment));

        $createAttachmentsCommand = new CreateAttachmentsCommand();
        $createAttachmentsCommand->attachments = $this->attachmentsInputDTOs();
        $createAttachmentsCommand->attachmentHolder = $this->attachmentHolder;

        $this->service->createAttachments($createAttachmentsCommand);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment loading failed, attachment not found.
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
     * @expectedExceptionMessage Unable to remove attachment.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenRemovesFile()
    {
        $this->attachmentRepository->expects($this->once())->method('get')
            ->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($this->attachment));

        $this->fileStorageService->expects($this->once())->method('remove')->with($this->equalTo(''))
            ->will($this->throwException(new \Exception()));

        $this->service->removeAttachment(self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     */
    public function thatAttachmentRemoves()
    {
        $attachment = new Attachment($this->file);

        $this->file->expects($this->once())->method('getFilename')->will($this->returnValue(self::DUMMY_FILENAME));

        $this->attachmentRepository->expects($this->once())->method('get')
            ->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($attachment));

        $this->fileStorageService->expects($this->once())->method('remove')->with($this->equalTo(self::DUMMY_FILENAME));

        $this->attachmentRepository->expects($this->once())->method('remove')->with($this->equalTo($attachment));

        $this->service->removeAttachment(self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @return FilesListDto
     */
    private function attachmentsInputDTOs()
    {
        $attachmentInput = new AttachmentInput();
        $attachmentInput->setFilename(self::DUMMY_FILENAME);
        $attachmentInput->setContent(self::DUMMY_FILE_CONTENT);
        return array($attachmentInput);
    }
}
