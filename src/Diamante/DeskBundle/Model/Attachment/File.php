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
        $pathname = basename($pathname);
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
        return urldecode($this->filename);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        $path     = $this->getPathname();
        $fileInfo = pathinfo($path);

        if (!array_key_exists('extension', $fileInfo)) {
            return '';
        }
        return strtolower($fileInfo['extension']);
    }

    //TODO: Refactor this part, as it is a fix for a dataaudit.
    public function __toString()
    {
        return $this->getLocation();
    }

    public function getLocation()
    {
        if (empty($this->pathname)) {
            return '';
        }

        $location = array_slice(explode("/", $this->getPathname()), -2);
        return sprintf("/%s/%s", $location[0], $location[1]);
    }
}
