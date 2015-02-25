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

namespace Diamante\DeskBundle\Api\Internal;

use Diamante\DeskBundle\Api\AttachmentService;
use Diamante\DeskBundle\Model\Attachment\AttachmentRepository;
use Diamante\DeskBundle\Model\Attachment\Manager;
use Symfony\Component\HttpFoundation\File\File;

class AttachmentServiceImpl implements AttachmentService
{
    /**
     * @var \Diamante\DeskBundle\Model\Attachment\AttachmentRepository
     */
    private $repository;
    /**
     * @var \Diamante\DeskBundle\Model\Attachment\Manager
     */
    private $manager;

    public function __construct(Manager $manager, AttachmentRepository $repository)
    {
        $this->repository = $repository;
        $this->manager = $manager;
    }

    /**
     * @param $hash
     * @return \Diamante\DeskBundle\Entity\Attachment|null
     */
    public function getByHash($hash)
    {
        return $this->repository->getByHash($hash);
    }

    /**
     * @param $hash
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getThumbnail($hash)
    {
        $file = $this->getByHash($hash);
        $location = dirname($file->getFile()->getPathname());
        $filename = sprintf('%s/thumbnail/%s.png', $location, $hash);

        return new File($filename);
    }
}