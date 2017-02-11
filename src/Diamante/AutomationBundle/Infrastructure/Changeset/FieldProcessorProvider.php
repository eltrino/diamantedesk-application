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

namespace Diamante\AutomationBundle\Infrastructure\Changeset;

use Diamante\AutomationBundle\Infrastructure\Changeset\FieldProcessor\DefaultProcessor;

/**
 * Class FieldProcessorProvider
 * @package Diamante\AutomationBundle\Infrastructure\Changeset
 */
class FieldProcessorProvider
{
    const PROCESSOR_PREFIX = '\Diamante\AutomationBundle\Infrastructure\Changeset\FieldProcessor\\';

    /**
     * @var array
     */
    protected $processors = [
        'attachments' => 'AttachmentsProcessor'
    ];

    /**
     * @param string $fieldName
     * @return FieldProcessor
     */
    public function provideProcessor($fieldName)
    {
        if (isset($this->processors[$fieldName])) {
            $className = (self::PROCESSOR_PREFIX . $this->processors[$fieldName]);
            if (!class_exists($className)) {
                throw new \RuntimeException('Field processor class not found.');
            }

            return new $className;
        };

        return new DefaultProcessor();
    }
}
