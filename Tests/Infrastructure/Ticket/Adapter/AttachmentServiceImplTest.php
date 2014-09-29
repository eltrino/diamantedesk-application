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
namespace Diamante\DeskBundle\Tests\Infrastructure\Ticket\Adapter;

use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Model\Attachment\File;
use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Infrastructure\Ticket\Adapter\AttachmentServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Api\Command\CreateAttachmentsCommand;

class AttachmentServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\DeskBundle\Api\AttachmentService
     * @Mock \Diamante\DeskBundle\Api\AttachmentService
     */
    private $attachmentContextService;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\AttachmentHolder
     * @Mock \Diamante\DeskBundle\Model\Attachment\AttachmentHolder
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
        $attachmentInputs = array(new AttachmentInput());

        $createAttachmentsCommand = new CreateAttachmentsCommand();
        $createAttachmentsCommand->attachments = $attachmentInputs;
        $createAttachmentsCommand->attachmentHolder = $this->attachmentHolder;

        $this->attachmentContextService->expects($this->once())->method('createAttachments')
            ->with($createAttachmentsCommand);

        $adapterAttachmentService = new AttachmentServiceImpl($this->attachmentContextService);
        $adapterAttachmentService->createAttachmentsForItHolder($attachmentInputs, $this->attachmentHolder);
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
