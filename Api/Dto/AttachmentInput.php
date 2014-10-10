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

    public static function createFromUploadedFile(\Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile)
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
}
