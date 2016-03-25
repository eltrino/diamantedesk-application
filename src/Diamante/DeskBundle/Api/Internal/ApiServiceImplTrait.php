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
use Diamante\DeskBundle\Api\Command\Shared\FilteringCommand;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Model\Shared\Filter\FilterCriteriaProcessor;
use Diamante\DeskBundle\Model\Shared\Filter\PagingInfo;
use Diamante\DeskBundle\Model\Shared\FilterableRepository;

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

    /**
     * @param FilterCriteriaProcessor $processor
     * @param FilterableRepository $repository
     * @param FilteringCommand $command
     * @param ApiPagingService $service
     * @param $countCallback
     *
     * @return \Diamante\DeskBundle\Model\Shared\Filter\PagingProperties
     */
    protected function buildPagination(
        FilterCriteriaProcessor $processor,
        FilterableRepository $repository,
        FilteringCommand $command,
        ApiPagingService $service,
        $countCallback = null
    ){
        $processor->setCommand($command);
        $criteria = $processor->getCriteria();
        $pagingProperties = $processor->getPagingProperties();

        $pagingInfo = $service->getPagingInfo($repository, $pagingProperties, $criteria, null, $countCallback);
        $this->populatePagingHeaders($service, $pagingInfo);

        return $pagingProperties;
    }
}
