<?php

namespace Eltrino\DiamanteDeskBundle\Tests\Stubs;

class TestFileInfo extends \SplFileInfo
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->fname = $name;

        $this->pathInfo = pathinfo($this->fname);
    }

    public function getPathname()
    {
        return $this->fname;
    }

    public function getRealPath()
    {
        return $this->pathInfo['dirname'] . '/' . $this->pathInfo['basename'];
    }
}
