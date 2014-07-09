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

namespace Eltrino\DiamanteDeskBundle\Tests\Ticket\Infrastructure\Adapter;

use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FileDto;
use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto;
use Eltrino\DiamanteDeskBundle\Attachment\Model\File;
use Eltrino\DiamanteDeskBundle\Entity\Attachment;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Adapter\AttachmentServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class AttachmentServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Eltrino\DiamanteDeskBundle\Attachment\Api\AttachmentService
     * @Mock \Eltrino\DiamanteDeskBundle\Attachment\Api\AttachmentService
     */
    private $attachmentContextService;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     * @Mock \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $uploadedFile;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentHolder
     * @Mock \Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentHolder
     */
    private $attachmentHolder;

    protected function setUp()
    {
        MockAnnotations::init($this);
    }

    /**
     * @test
     */
    public function thatAttachmentCreatesForTicket()
    {
        $filesListDto = new FilesListDto();
        $filesListDto->setFiles(array(new FileDto()));

        $this->attachmentContextService->expects($this->once())->method('createAttachments')
            ->with($this->equalTo($filesListDto), $this->equalTo($this->attachmentHolder));

        $adapterAttachmentService = new AttachmentServiceImpl($this->attachmentContextService);
        $adapterAttachmentService->createAttachmentsForItHolder($filesListDto, $this->attachmentHolder);
    }

    /**
     * @test
     */
    public function thatAttachmentRemovesFromTicket()
    {
        $attachment = new Attachment(new File('filename.ext'));
        $adapterAttachmentService = new AttachmentServiceImpl($this->attachmentContextService);
        $adapterAttachmentService->removeAttachmentFromItHolder($attachment);
    }
}
