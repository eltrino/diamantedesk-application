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
use Eltrino\DiamanteDeskBundle\Model\Ticket\Status;

class StatusTransformer implements DataTransformerInterface
{
    private $statusOptions = array();

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
        if (null === $status || (false === ($status instanceof Status))) {
            return '';
        }

        return $status->getValue();
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
                array(
                    Status::NEW_ONE     => Status::LABEL_NEW_ONE,
                    Status::OPEN        => Status::LABEL_OPEN,
                    Status::PENDING     => Status::LABEL_PENDING,
                    Status::IN_PROGRESS => Status::LABEL_IN_PROGRESS,
                    Status::CLOSED      => Status::LABEL_CLOSED,
                    Status::ON_HOLD     => Status::LABEL_ON_HOLD
                );
        }
        return $this->statusOptions;
    }
}
