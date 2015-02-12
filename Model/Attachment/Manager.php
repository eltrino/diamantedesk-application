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
namespace Diamante\DeskBundle\Model\Attachment;

interface Manager
{
    /**
     * Create new attachment and assign it to the attachment holder
     * @param string $filename
     * @param string $content
     * @param AttachmentHolder $holder
     * @return \Diamante\DeskBundle\Model\Attachment\Attachment
     */
    public function createNewAttachment($filename, $content, AttachmentHolder $holder);

    /**
     * Delete attachment
     * @param Attachment $attachment
     * @return void
     */
    public function deleteAttachment(Attachment $attachment);
}
