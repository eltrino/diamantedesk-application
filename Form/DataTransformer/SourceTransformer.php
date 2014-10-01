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
use Diamante\DeskBundle\Model\Ticket\Source;

class SourceTransformer implements DataTransformerInterface
{
    private $sourceOptions = array();

    public function __construct()
    {
        $this->sourceOptions = $this->getOptions();
    }

    /**
     * @param mixed $source
     * @return mixed|string
     */
    public function transform($source)
    {
        if (null === $source || (false === ($source instanceof Source))) {
            return '';
        }

        return $source->getValue();
    }

    /**
     * @param mixed $source
     * @return mixed|null
     */
    public function reverseTransform($source)
    {
        if ('' === $source) {
            return null;
        }

        return $source;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->sourceOptions)) {
            $this->sourceOptions =
                array(
                    Source::PHONE   => Source::LABEL_PHONE,
                    Source::EMAIL   => Source::LABEL_EMAIL,
                    Source::WEB     => Source::LABEL_WEB
                );
        }
        return $this->sourceOptions;
    }
}
