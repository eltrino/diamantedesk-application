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
namespace Diamante\DeskBundle\Model\Attachment;

use Diamante\DeskBundle\Model\Shared\Repository;

class ManagerImpl implements Manager
{
    /**
     * @var Services\FileStorageService
     */
    private $fileStorageService;

    /**
     * @var AttachmentFactory
     */
    private $factory;

    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Services\FileStorageService $fileStorageService,
        AttachmentFactory $factory,
        Repository $repository
    ) {
        $this->fileStorageService = $fileStorageService;
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * Create new attachment and assign it to the attachment holder
     * @param string $filename
     * @param string $content
     * @param AttachmentHolder $holder
     * @return \Diamante\DeskBundle\Model\Attachment\Attachment
     */
    public function createNewAttachment($filename, $content, AttachmentHolder $holder)
    {
        $this->validateFilename($filename);
        $this->validateContent($content);

        $filenamePrefix = $this->exposeFilenamePrefixFrom($holder);

        $path = $this->fileStorageService->upload($filenamePrefix . '/' . $filename, $content);

        $file = new File($path);

        $attachment = $this->factory->create($file);

        $holder->addAttachment($attachment);
        $this->repository->store($attachment);

        return $attachment;
    }

    /**
     * Delete attachment
     * @param Attachment $attachment
     * @return void
     */
    public function deleteAttachment(Attachment $attachment)
    {
        $this->fileStorageService->remove($attachment->getFilename());
        $this->repository->remove($attachment);
    }

    private function validateFilename($filename)
    {
        if (false === is_string($filename)) {
            throw new \LogicException('Given filename is invalid.');
        }
    }

    private function validateContent($content)
    {
        if (false === is_string($content)) {
            throw new \LogicException('Given file content is invalid.');
        }
    }

    /**
     * @param AttachmentHolder $attachmentHolder
     * @return string
     */
    private function exposeFilenamePrefixFrom(AttachmentHolder $attachmentHolder)
    {
        $parts = explode("\\", get_class($attachmentHolder));
        $prefix = strtolower(array_pop($parts));
        return $prefix;
    }
}
