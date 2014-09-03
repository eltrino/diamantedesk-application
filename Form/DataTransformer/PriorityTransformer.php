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

namespace Eltrino\DiamanteDeskBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Priority;

class PriorityTransformer implements DataTransformerInterface
{
    private $priorityOptions = array();

    public function __construct()
    {
        $this->priorityOptions = $this->getOptions();
    }

    /**
     * @param mixed $priority
     * @return mixed|string
     */
    public function transform($priority)
    {
        if (null === $priority || (false === ($priority instanceof Priority))) {
            return '';
        }

        return $priority->getValue();
    }

    /**
     * @param mixed $priority
     * @return mixed|null
     */
    public function reverseTransform($priority)
    {
        if ('' === $priority) {
            return null;
        }

        return $priority;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->priorityOptions)) {
            $this->priorityOptions =
                array(
                    Priority::PRIORITY_LOW => Priority::PRIORITY_LOW_LABEL,
                    Priority::PRIORITY_MEDIUM => Priority::PRIORITY_MEDIUM_LABEL,
                    Priority::PRIORITY_HIGH => Priority::PRIORITY_HIGH_LABEL
                );
        }
        return $this->priorityOptions;
    }
}