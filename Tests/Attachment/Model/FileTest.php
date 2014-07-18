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
namespace Eltrino\DiamanteDeskBundle\Tests\Attachment\Model;

use Eltrino\DiamanteDeskBundle\Attachment\Model\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var File
     */
    private $file;

    public function testGetExtensionWhenFileWithoutExt()
    {
        $this->file = new File('test');

        $ext = $this->file->getExtension();
        $this->assertEquals('', $ext);
    }

    public function testGetExtensionWithLargeLetter()
    {
        $this->file = new File('test.Ext');

        $ext = $this->file->getExtension();
        $this->assertEquals('ext', $ext);
    }

    public function testGetExtension()
    {
        $this->file = new File('test.ext');

        $ext = $this->file->getExtension();
        $this->assertEquals('ext', $ext);
    }
}