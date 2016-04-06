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
namespace Diamante\DeskBundle\Infrastructure\Branch;

use Symfony\Component\Filesystem\Filesystem;
use Diamante\DeskBundle\Model\Branch\Exception\LogoHandlerLogicException;
use Diamante\DeskBundle\Model\Branch\Logo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 *
 * Class BranchLogoHandler
 * @package Diamante\DeskBundle\Model
 */
class BranchLogoHandler
{
    /**
     * @var \SplFileInfo
     */
    private $branchLogoDir;

    private $filesystem;

    /**
     * @var array
     */
    private $permittedMimeTypes = array();

    public function __construct(\SplFileInfo $branchLogoDir, Filesystem $filesystem)
    {
        $this->branchLogoDir = $branchLogoDir;
        $this->filesystem = $filesystem;
        $this->permittedMimeTypes = array(
            'image/gif',
            'image/jpeg',
            'image/png'
        );
    }

    /**
     * Upload (move) file to branch logos directory
     * @param UploadedFile $logo
     * @param null|string $targetFilename
     * @return \Symfony\Component\HttpFoundation\File\File
     * @throws LogoHandlerLogicException
     */
    public function upload(UploadedFile $logo, $targetFilename = null)
    {
        if (!in_array($logo->getMimeType(), $this->permittedMimeTypes)) {
            throw new LogoHandlerLogicException(sprintf('"%s" file type is not permitted. Use images for logo and try again.', $logo->getMimeType()));
        }

        if (is_null($targetFilename)) {
            $targetFilename = sha1(uniqid(mt_rand(), true)) . '.' . $logo->guessExtension();
        }

        if (false === $this->branchLogoDir->isDir() || false === $this->branchLogoDir->isWritable()) {
            throw new \RuntimeException(sprintf("Branch logo directory (%s) is not writable, doesn't exist or no space left on the disk.", $this->branchLogoDir->getRealPath()));
        }

        return $logo->move($this->branchLogoDir->getRealPath(), $targetFilename);
    }

    /**
     * @param Logo $logo
     */
    public function remove(Logo $logo)
    {
        $this->filesystem
            ->remove(
                $this->branchLogoDir->getRealPath() . '/' . $logo->getName()
            );
    }

    public static function create($kernelRootDir, Filesystem $filesystem)
    {
        $branchLogoDir = realpath($kernelRootDir . '/../web') . Logo::PATH_TO_LOGO_DIR;
        if (!$filesystem->exists($branchLogoDir)) {
            $filesystem->mkdir($branchLogoDir);
        }
        $branchLogoDir = new \SplFileInfo($branchLogoDir);
        if (!$branchLogoDir->isWritable()) {
            $filesystem->chmod($branchLogoDir->getRealPath(), 0777);
        }

        return new BranchLogoHandler($branchLogoDir, $filesystem);
    }
}
