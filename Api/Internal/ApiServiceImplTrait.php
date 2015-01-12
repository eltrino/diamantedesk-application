<?php

namespace Diamante\DeskBundle\Api\Internal;

use Diamante\DeskBundle\Api\Dto\AttachmentInput;

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
                $attachmentInputs[] = AttachmentInput::createFromString($each);
            }
            $command->attachmentsInput = $attachmentInputs;
        }
    }
}
