<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\Api\Internal\Shared;


use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Model\Attachment\AttachmentHolder;

trait AttachmentTrait
{
    /**
     * @param $command
     * @param AttachmentHolder $entity
     * @return array
     */
    protected function createAttachments($command, AttachmentHolder $entity)
    {
        $attachments = [];

        if (!empty($command->attachmentsInput)) {
            foreach ($command->attachmentsInput as $each) {
                /** @var AttachmentInput $each */
                $attachments[] = $this->attachmentManager->createNewAttachment($each->getFilename(), $each->getContent(), $entity);
            }
        }

        return $attachments;
    }
}