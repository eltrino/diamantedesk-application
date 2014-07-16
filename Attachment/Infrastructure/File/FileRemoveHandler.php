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
namespace Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\File;

use Symfony\Component\Filesystem\Filesystem;

class FileRemoveHandler
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var \SplFileInfo
     */
    private $uploadDir;

    public function __construct(Filesystem $fs, \SplFileInfo $uploadDir)
    {
        $this->fs = $fs;
        $this->uploadDir = $uploadDir;
    }

    /**
     * @param $filename
     * @throws \LogicException if given filename is empty string
     */
    public function remove($filename)
    {
        if (empty($filename)) {
            throw new \LogicException('Attachment validation failed, filename cannot be empty.');
        }
        $this->fs->remove($this->uploadDir->getRealPath() . '/' . $filename);
    }

    public static function create($kernelRootDir)
    {
        return new FileRemoveHandler(
            new Filesystem(),
            new \SplFileInfo(realpath(
                $kernelRootDir . '/' . \Eltrino\DiamanteDeskBundle\Attachment\Model\Attachment::ATTACHMENTS_DIRECTORY
            ))
        );
    }
}
