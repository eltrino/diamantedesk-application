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

use Diamante\DeskBundle\Api\ApiPagingService;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Model\Shared\Filter\PagingInfo;

trait ApiServiceImplTrait
{
    /**
     * @param $command
     * @return void
     */
    protected function prepareAttachmentInput($command)
    {
        if ($command->attachmentsInput && is_array($command->attachmentsInput)) {
            $attachmentInputs = array();
            foreach ($command->attachmentsInput as $each) {
                $input = $this->decodeAttachmentInput($each);
                $attachmentInputs[] = AttachmentInput::createFromArray($input);
            }
            $command->attachmentsInput = $attachmentInputs;
        }
    }

    /**
     * @param array $input
     * @return array
     */
    private function decodeAttachmentInput($input)
    {
        if (false === isset($input['filename']) || false === isset($input['content'])) {
            throw new \InvalidArgumentException('Attachment input string is invalid.');
        }
        $input['content'] = base64_decode($input['content']);
        return $input;
    }

    /**
     * @param ApiPagingService $service
     * @param PagingInfo $info
     */
    protected function populatePagingHeaders(ApiPagingService $service, PagingInfo $info)
    {
        $links = $service->createPagingLinks($info);
        $service->populatePagingHeaders($info, $links);
    }
}
