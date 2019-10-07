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

namespace Diamante\DeskBundle\Form\DataTransformer;

use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachmentTransformer implements DataTransformerInterface
{
    /**
     * Return empty string every time for file type input field. No editing
     * @param mixed $value
     * @return string
     */
    public function transform($value)
    {
        return '';
    }

    /**
     * Transform Symfony UploadedFile into AttachmentInput
     * @param array $value
     * @return array The array of AttachmentInput transformed from Symfony UploadedFile
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $inputs = array();

        if (!$value) {
            return $inputs;
        }

        foreach ($value as $each) {
            if ($each) {
                if (false === ($each instanceof UploadedFile)) {
                    throw new TransformationFailedException(
                        'Every item in input files array should be an instance of the UploadedFile.'
                    );
                }
                $attachment = AttachmentInput::createFromUploadedFile($each);
                array_push($inputs, $attachment);
            }
        }
        return $inputs;
    }
}
