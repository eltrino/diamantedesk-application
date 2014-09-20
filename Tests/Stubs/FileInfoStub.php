<?php

namespace Eltrino\DiamanteDeskBundle\Tests\Stubs;

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
