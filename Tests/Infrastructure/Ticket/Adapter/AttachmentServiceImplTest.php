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
namespace Eltrino\DiamanteDeskBundle\Tests\Infrastructure\Ticket\Adapter;

use Eltrino\DiamanteDeskBundle\Api\Dto\AttachmentInput;
use Eltrino\DiamanteDeskBundle\Model\Attachment\File;
use Eltrino\DiamanteDeskBundle\Model\Attachment\Attachment;
use Eltrino\DiamanteDeskBundle\Infrastructure\Ticket\Adapter\AttachmentServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Eltrino\DiamanteDeskBundle\Api\Command\CreateAttachmentsCommand;

class AttachmentServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Eltrino\DiamanteDeskBundle\Api\AttachmentService
     * @Mock \Eltrino\DiamanteDeskBundle\Api\AttachmentService
     */
    private $attachmentContextService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Attachment\AttachmentHolder
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Attachment\AttachmentHolder
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
