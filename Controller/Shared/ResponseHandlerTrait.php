<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\Controller\Shared;


use Diamante\DeskBundle\Api\Dto\AttachmentDto;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait ResponseHandlerTrait
{
    /**
     * @param $saveAndStay
     * @param $saveAndClose
     * @param array $params
     * @return RedirectResponse
     */
    protected function getSuccessSaveResponse($saveAndStay, $saveAndClose, $params = array())
    {
        return $this->get('oro_ui.router')->redirectAfterSave(
            ['route' => $saveAndStay, 'parameters' => $params],
            ['route' => $saveAndClose, 'parameters' => $params]
        );
    }

    /**
     * @param AttachmentDto $attachmentDto
     * @return BinaryFileResponse
     */
    protected function getFileDownloadResponse(AttachmentDto $attachmentDto)
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