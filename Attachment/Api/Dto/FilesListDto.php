<?php
/**
 * Created by PhpStorm.
 * User: Ruslan Voitenko
 * Date: 7/7/14
 * Time: 8:12 PM
 */

namespace Eltrino\DiamanteDeskBundle\Attachment\Api\Dto;

class FilesListDto
{
    /**
     * @var FileDto[]
     */
    private $files;

    /**
     * @param array $files
     */
    public function setFiles(array $files)
    {
        \Assert\that($files)->all()->isInstanceOf('Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FileDto');
        $this->files = $files;
    }

    /**
     * @return FileDto[]
     */
    public function getFiles()
    {
        return $this->files;
    }
}
