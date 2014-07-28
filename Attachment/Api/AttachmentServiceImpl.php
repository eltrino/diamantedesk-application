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
namespace Eltrino\DiamanteDeskBundle\Attachment\Api;

use Doctrine\ORM\EntityManager;
use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto;
use Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\File\FileRemoveHandler;
use Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\FileUpload\FileUploadHandler;
use Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentFactory;
use Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentHolder;
use Eltrino\DiamanteDeskBundle\Attachment\Model\AttachmentRepository;
use Eltrino\DiamanteDeskBundle\Attachment\Model\File;
use Eltrino\DiamanteDeskBundle\Attachment\Model\Services\FileStorageService;

class AttachmentServiceImpl implements AttachmentService
{
    /**
     * @var AttachmentRepository
     */
    private $attachmentRepository;

    /**
     * @var AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @var FileStorageService
     */
    private $fileStorageService;

    public function __construct(
        AttachmentFactory $attachmentFactory,
        AttachmentRepository $attachmentRepository,
        FileStorageService $fileStorageService
    ) {
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentRepository = $attachmentRepository;
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Create Attachments
     * @param FilesListDto $filesList
     * @param AttachmentHolder $attachmentHolder
     * @return void
     */
    public function createAttachments(array $attachmentsInput, AttachmentHolder $attachmentHolder)
    {
        \Assert\that($attachmentsInput)->all()
            ->isInstanceOf('Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\AttachmentInput');
        $filenamePrefix = $this->exposeFilenamePrefixFrom($attachmentHolder);
        foreach ($attachmentsInput as $attachmentInput) {
            try {
                $path = $this->fileStorageService->upload(
                    $filenamePrefix . '/' . $attachmentInput->getFilename(), $attachmentInput->getContent()
                );

                $file = new File($path);

                $attachment = $this->attachmentFactory->create($file);

                $attachmentHolder->addAttachment($attachment);
                $this->attachmentRepository->store($attachment);
            } catch (\RuntimeException $e) {
                /**
                 * @todo logging
                 */
                throw $e;
            }
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

    /**
     * Remove Attachment
     * @param integer $attachmentId
     * @return void
     * @throws \RuntimeException if error occures during removing
     */
    public function removeAttachment($attachmentId)
    {
        $attachment = $this->attachmentRepository->get($attachmentId);
        if (is_null($attachment)) {
            throw new \RuntimeException('Attachment loading failed, attachment not found.');
        }
        try {
            $this->fileStorageService->remove($attachment->getFilename());
            $this->attachmentRepository->remove($attachment);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to remove attachment.', 0, $e);
        }
    }

    public static function create(
        EntityManager $em,
        AttachmentFactory $attachmentFactory,
        FileStorageService $fileStorageService
    ) {
        return new AttachmentServiceImpl(
            $attachmentFactory,
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Attachment'),
            $fileStorageService
        );
    }
}
