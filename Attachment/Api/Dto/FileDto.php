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
namespace Eltrino\DiamanteDeskBundle\Attachment\Api\Dto;

class FileDto
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $data;

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
     * @param string $data
     */
    public function setData($data)
    {
        \Assert\that($data)->notEmpty()->string();
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    public static function createFromUploadedFile(\Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile)
    {
        $dto = new FileDto();
        $dto->setFilename($uploadedFile->getClientOriginalName());
        $data = '';
        $file = $uploadedFile->openFile();
        $file->rewind();
        while (false === $file->eof()) {
            $data .= $file->fgets();
        }
        $dto->setData($data);
        return $dto;
    }
}
