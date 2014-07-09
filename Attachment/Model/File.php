<?php
/**
 * Created by PhpStorm.
 * User: Ruslan Voitenko
 * Date: 7/7/14
 * Time: 7:04 PM
 */

namespace Eltrino\DiamanteDeskBundle\Attachment\Model;

class File
{
    /**
     * @var string
     */
    private $pathname;

    /**
     * @var string
     */
    private $filename;

    public function __construct($pathname)
    {
        $this->pathname = $pathname;
        $this->filename = strrpos($pathname, '/') !== false ? substr($pathname, strrpos($pathname, '/') + 1) : $pathname;
    }

    /**
     * @return string
     */
    public function getPathname()
    {
        return $this->pathname;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
