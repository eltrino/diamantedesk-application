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
namespace Diamante\DeskBundle\Infrastructure\Attachment\Imagine\Data\Loader;

use Gedmo\Uploadable\MimeType\MimeTypeGuesser;
use Liip\ImagineBundle\Binary\Loader\FileSystemLoader;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileSystemAttachmentLoader extends FileSystemLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @param ImagineInterface $imagine
     */
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
        $this->formats = array();
        $this->rootPath = '';
    }

    /**
     * @return ImagineInterface
     */
    public function getImagine()
    {
        return $this->imagine;
    }

    /**
     * Get the file info for the given path.
     *
     * This can optionally be used to generate the given file.
     *
     * @param string $absolutePath
     *
     * @return \SplFileInfo
     */
    protected function getFileInfo($absolutePath)
    {
        return new \SplFileInfo($absolutePath);
    }


    /**
     * {@inheritDoc}
     */
    public function find($path)
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        $file = $this->rootPath.'/'.ltrim($path, '/');
        $info = $this->getFileInfo($file);
        $absolutePath = $info->getPath().DIRECTORY_SEPARATOR.$info->getFilename();

        $trimString = sprintf('.%s', $info->getExtension());
        $name = rtrim($info->getPath().DIRECTORY_SEPARATOR.$info->getFilename(), $trimString);

        $targetFormat = null;
        // set a format if an extension is found and is allowed
        $extension = $info->getExtension();
        if (isset($extension)
            && (empty($this->formats) || in_array($extension, $this->formats))
        ) {
            $targetFormat = $extension;
        }

        if (empty($targetFormat) || !file_exists($absolutePath)) {
            // attempt to determine path and format
            $absolutePath = null;
            foreach ($this->formats as $format) {
                if ($targetFormat !== $format && file_exists($name.'.'.$format)) {
                    $absolutePath = $name.'.'.$format;

                    break;
                }
            }

            if (!$absolutePath) {
                if (!empty($targetFormat) && is_file($name)) {
                    $absolutePath = $name;
                } else {
                    throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $file));
                }
            }
        }

        return $this->imagine->open($absolutePath);
    }
}
