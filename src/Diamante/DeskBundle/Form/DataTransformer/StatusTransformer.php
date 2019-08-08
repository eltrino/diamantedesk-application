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

use Symfony\Component\Form\DataTransformerInterface;
use Diamante\DeskBundle\Model\Ticket\Status;

class StatusTransformer implements DataTransformerInterface
{
    private $statusOptions;

    public function __construct()
    {
        $this->statusOptions = $this->getOptions();
    }

    /**
     * @param mixed $status
     * @return mixed|string
     */
    public function transform($status)
    {
        if ($status instanceof Status) {
            return $status->getValue();
        }

        if (array_key_exists($status, $this->statusOptions)) {
            return $status;
        }

        return '';
    }

    /**
     * @param mixed $status
     * @return mixed|null
     */
    public function reverseTransform($status)
    {
        if ('' === $status) {
            return null;
        }

        return $status;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->statusOptions)) {
            $this->statusOptions =
                [
                    Status::LABEL_NEW_ONE => Status::NEW_ONE,
                    Status::LABEL_OPEN => Status::OPEN,
                    Status::LABEL_PENDING => Status::PENDING,
                    Status::LABEL_IN_PROGRESS => Status::IN_PROGRESS,
                    Status::LABEL_CLOSED => Status::CLOSED,
                    Status::LABEL_ON_HOLD => Status::ON_HOLD,
                ];
        }

        return $this->statusOptions;
    }
}
