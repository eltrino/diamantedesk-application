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

namespace Eltrino\DiamanteDeskBundle\Tests\Attachment\Infrastructure\Persistance\Doctrine;

use Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\Persistence\Doctrine\DoctrineAttachmentRepository;
use Eltrino\DiamanteDeskBundle\Entity\Attachment;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class DoctrineAttachmentRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineAttachmentRepository
     */
    private $repository;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     * @Mock \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $classMetadata;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->repository = new DoctrineAttachmentRepository($this->em, $this->classMetadata);
    }

    /**
     * @test
     */
    public function thatAttachmentStores()
    {
        $attachment = new Attachment('dummy_filename.ext');
        $this->em->expects($this->once())->method('persist')->with($this->equalTo($attachment));
        $this->em->expects($this->once())->method('flush');

        $this->repository->store($attachment);
    }

    /**
     * @test
     */
    public function thatAttachmentRemoves()
    {
        $attachment = new Attachment('dummy_filename.ext');
        $this->em->expects($this->once())->method('remove')->with($this->equalTo($attachment));
        $this->em->expects($this->once())->method('flush');

        $this->repository->remove($attachment);
    }
}
