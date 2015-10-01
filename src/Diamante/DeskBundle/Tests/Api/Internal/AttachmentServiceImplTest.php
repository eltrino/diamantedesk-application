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

namespace Api\Internal;

use Diamante\DeskBundle\Api\Internal\AttachmentServiceImpl;
use Diamante\DeskBundle\Entity\Attachment;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class AttachmentServiceImplTest extends \PHPUnit_Framework_TestCase
{

    const DUMMY_FILE_NAME = 'file.dum';
    const DUMMY_HASH      = '431b8b2d40f471e06bdb46ee547aa3af';
    /**
     * @var \Diamante\DeskBundle\Model\Attachment\AttachmentRepository
     * @Mock Diamante\DeskBundle\Model\Attachment\AttachmentRepository
     */
    private $repository;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\Manager
     * @Mock Diamante\DeskBundle\Model\Attachment\Manager
     */
    private $manager;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\File
     * @Mock Diamante\DeskBundle\Model\Attachment\File
     */
    private $file;

    /**
     * @var AttachmentServiceImpl
     */
    private $service;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new AttachmentServiceImpl($this->manager, $this->repository);
    }

    /**
     * @test
     */
    public function testGetByHash()
    {
        $this->repository
            ->expects($this->once())
            ->method('getByHash')
            ->with($this->equalTo(self::DUMMY_HASH))
            ->will($this->returnValue($this->getDummyAttachment()));

        $this->file
            ->expects($this->atLeastOnce())
            ->method('getFilename')
            ->will($this->returnValue(self::DUMMY_FILE_NAME));

        $attach = $this->service->getByHash(self::DUMMY_HASH);

        $this->assertEquals(self::DUMMY_HASH, $attach->getHash());
        $this->assertEquals(self::DUMMY_FILE_NAME, $attach->getFile()->getFilename());
    }

    private function getDummyAttachment()
    {
        return new Attachment(
            $this->file,
            self::DUMMY_HASH
        );
    }
}