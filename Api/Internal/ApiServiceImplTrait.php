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
                $input = $this->decodeAttachmentInput($each);
                $attachmentInputs[] = AttachmentInput::createFromArray($input);
            }
            $command->attachmentsInput = $attachmentInputs;
        }
    }

    /**
     * @param string $input
     * @return array
     */
    private function decodeAttachmentInput($input)
    {
        $input = json_decode($input, true);
        if (false == isset($input['filename']) || false == isset($input['content'])) {
            throw new \InvalidArgumentException('Attachment input string is invalid.');
        }
        $input['content'] = base64_decode($input['content']);
        return $input;
    }
}
