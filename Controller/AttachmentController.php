<?php

namespace Diamante\DeskBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use \Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Diamante\DeskBundle\Api\Dto\AttachmentDto;

/**
 * @Route("attachments")
 */
class AttachmentController extends Controller
{
    /**
     * @Route(
     *      "/download/file/{hash}",
     *      name="diamante_attachment_file_download",
     *      requirements={"hash"="\w+"}
     * )
     *
     * @param string $hash
     * @return BinaryFileResponse
     */
    public function fileAttachmentAction($hash)
    {
        $attachmentService = $this->get('diamante.attachment.service');
        try {
            $attachment = $attachmentService->getByHash($hash);
            $attachmentDto = AttachmentDto::createFromAttachment($attachment);
            $response = $this->getFileDownloadResponse($attachmentDto);
            return $response;
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(sprintf('Attachment loading failed: %s', $e->getMessage()));
            throw $this->createNotFoundException('Attachment not found');
        }
    }

    /**
     * @Route(
     *      "/download/thumbnail/{hash}",
     *      name="diamante_attachment_thumbnail_download",
     *      requirements={"hash"="\w+"}
     * )
     *
     * @param string $hash
     * @return BinaryFileResponse
     */
    public function thumbnailAttachmentAction($hash)
    {
        $attachmentService = $this->get('diamante.attachment.service');
        try {
            $file = $attachmentService->getThumbnail($hash);
            $attachmentDto = new AttachmentDto();
            $attachmentDto->setFileName($file->getFilename());
            $attachmentDto->setFilePath($file->getPathname());
            $response = $this->getFileDownloadResponse($attachmentDto);
            return $response;
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(sprintf('Attachment loading failed: %s', $e->getMessage()));
            throw $this->createNotFoundException('Attachment not found');
        }
    }

    /**
     * @param AttachmentDto $attachmentDto
     * @return BinaryFileResponse
     */
    private function getFileDownloadResponse(AttachmentDto $attachmentDto)
    {
        $response = new BinaryFileResponse($attachmentDto->getFilePath());
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $attachmentDto->getFileName(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $attachmentDto->getFileName())
        );

        return $response;
    }
}
