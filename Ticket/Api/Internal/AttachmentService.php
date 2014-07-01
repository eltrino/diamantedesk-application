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

namespace Eltrino\DiamanteDeskBundle\Ticket\Api\Internal;

use Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentHolder;
use Eltrino\DiamanteDeskBundle\Entity\Attachment;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Comment;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface AttachmentService
{
    /**
     * Creates Attachment for Holder
     * @param UploadedFile $file
     * @param AttachmentHolder $holder
     * @return void
     */
    public function createAttachmentForItHolder(UploadedFile $file, AttachmentHolder $holder);

    /**
     * Removes Attachment
     * @param Attachment $attachment
     * @return void
     */
    public function removeAttachmentFromItHolder(Attachment $attachment);
}
