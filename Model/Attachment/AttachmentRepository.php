<?php


namespace Diamante\DeskBundle\Model\Attachment;

interface AttachmentRepository
{
    /**
     * @param string $hash
     * @return Attachment
     */
    public function getByHash($hash);
}