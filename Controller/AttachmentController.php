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
     *      "/download/image/{hash}",
     *      name="diamante_attachment_image_download",
     *      requirements={"hash"="\w+"}
     * )
     *
     * @param string $hash
     * @return BinaryFileResponse
     */
    public function imageAttachmentAction($hash)
    {
        $attachmentService = $this->get('diamante.attachment.service');
        try {
            $attachment = $attachmentService->getByHash($hash);
            $attachmentDto = AttachmentDto::createFromAttachment($attachment);
            $response = $this->getFileDownloadResponse($attachmentDto);
            return $response;
        } catch (\Exception $e) {
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
            throw $this->createNotFoundException('Attachment not found');
        }
    }

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
