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

use Diamante\UserBundle\Model\User;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class UserTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_object($value)) {
            throw new UnexpectedTypeException($value, 'object');
        }

        return (string)$value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $value;
        } elseif(preg_match('/^(diamante|oro)_\d+$/i', $value)) {
            return User::fromString($value);
        } else {
            return null;
        }
    }
} 