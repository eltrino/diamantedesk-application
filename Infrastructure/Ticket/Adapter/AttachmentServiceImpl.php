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
namespace Diamante\DeskBundle\Infrastructure\Ticket\Adapter;

use Diamante\DeskBundle\Model\Attachment\AttachmentHolder;
use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Api\AttachmentService;
use Diamante\DeskBundle\Api\Command\CreateAttachmentsCommand;
use Diamante\DeskBundle\Model\Ticket\AttachmentService as TicketAttachmentService;

class AttachmentServiceImpl implements TicketAttachmentService
{
    /**
     * @var \Diamante\DeskBundle\Api\AttachmentService
     */
    private $attachmentContextService;

    public function __construct(AttachmentService $attachmentContextService)
    {
        $this->attachmentContextService = $attachmentContextService;
    }

    /**
     * Creates Attachments for Holder
     * @param array $attachmentsInput array of AttachmentInput DTOs
     * @param AttachmentHolder $holder
     * @return void
     */
    public function createAttachmentsForItHolder(array $attachmentsInput, AttachmentHolder $holder)
    {
        $createAttachmentsCommand = new CreateAttachmentsCommand();
        $createAttachmentsCommand->attachments = $attachmentsInput;
        $createAttachmentsCommand->attachmentHolder = $holder;
        $this->attachmentContextService->createAttachments($createAttachmentsCommand);
    }

    /**
     * Removes Attachment
     * @param Attachment $attachment
     * @return void
     */
    public function removeAttachmentFromItHolder(Attachment $attachment)
    {
        $this->attachmentContextService->removeAttachment($attachment->getId());
    }
}
