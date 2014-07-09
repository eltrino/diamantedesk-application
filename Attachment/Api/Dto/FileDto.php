<?php
/**
 * Created by PhpStorm.
 * User: Ruslan Voitenko
 * Date: 7/7/14
 * Time: 7:29 PM
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
