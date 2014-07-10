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
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;

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

        return array_search($status, $this->statusOptions);
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

        return $this->statusOptions[$status];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->statusOptions)) {
            $this->statusOptions =
                array(
                    0 => Status::NEW_ONE,
                    1 => Status::OPEN,
                    2 => Status::PENDING,
                    3 => Status::IN_PROGRESS,
                    4 => Status::CLOSED,
                    5 => Status::ON_HOLD
                );
        }
        return $this->statusOptions;
    }
} 