<?php

namespace Diamante\DeskBundle\Api\Dto;

use Diamante\DeskBundle\Model\Attachment\Attachment;

class AttachmentDto
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param mixed $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = realpath($filePath);
    }

    /**
     * @param Attachment $attachment
     * @returns AttachmentDto
     */
    public static function createFromAttachment(Attachment $attachment)
    {
        $dto = new self();
        $dto->setFilename($attachment->getFilename());
        $dto->setFilePath($attachment->getFile()->getPathname());

        return $dto;
    }
}