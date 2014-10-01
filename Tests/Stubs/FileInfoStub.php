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
namespace Diamante\DeskBundle\Tests\Stubs;

class FileInfoStub extends \SplFileInfo
{
    protected $state;

    public function __construct($name, $isDir, $isWritable)
    {
        parent::__construct($name);
        $this->fname = $name;

        $this->pathInfo = pathinfo($this->fname);

        $this->isDir = $isDir;
        $this->isWritable = $isWritable;
    }

    public function getPathname()
    {
        return $this->fname;
    }

    public function getRealPath()
    {
        return $this->pathInfo['dirname'] . '/' . $this->pathInfo['basename'];
    }

    public function isDir()
    {
        return $this->isDir;
    }

    public function isWritable()
    {
        return $this->isWritable;
    }
}
