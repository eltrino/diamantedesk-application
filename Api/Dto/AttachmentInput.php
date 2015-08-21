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
namespace Diamante\DeskBundle\Api\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachmentInput
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $content;

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        \Assert\that($filename)->notEmpty()->string();
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        \Assert\that($content)->notEmpty()->string();
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return AttachmentInput
     */
    public static function createFromUploadedFile(UploadedFile $uploadedFile)
    {
        $dto = new self();
        $dto->setFilename($uploadedFile->getClientOriginalName());
        $content = '';
        $file = $uploadedFile->openFile();
        $file->rewind();
        while (false === $file->eof()) {
            $content .= $file->fgets();
        }
        $dto->setContent($content);
        return $dto;
    }

    /**
     * @param array $input
     * @return AttachmentInput
     */
    public static function createFromArray(array $input)
    {
        if (false === isset($input['filename']) || false === isset($input['content'])) {
            throw new \InvalidArgumentException('Not all required fields exist in array.');
        }
        $dto = new self();
        $dto->setFilename($input['filename']);
        $dto->setContent($input['content']);
        return $dto;
    }
}
