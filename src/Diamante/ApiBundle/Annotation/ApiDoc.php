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

namespace Diamante\ApiBundle\Annotation;

/**
 * @Annotation
 */
class ApiDoc extends \Nelmio\ApiDocBundle\Annotation\ApiDoc
{
    /** @var string */
    private $method;

    /** @var string */
    private $uri;

    public function __construct(array $data)
    {
        parent::__construct($data);

        if (isset($data['method'])) {
            $this->method = $data['method'];
        } else {
            $this->method = 'ANY';
        }

        if (isset($data['uri'])) {
            $this->uri = $data['uri'];
        } else {
            throw new \LogicException('@ApiDoc annotation should contain required parameter "uri".');
        }
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return array
     */
    public function getRouteRequirements()
    {
        $requirements = array();

        foreach ($this->getRequirements() as $key => $item) {
            if (array_key_exists('requirement', $item)) {
                $requirements[$key] = $item['requirement'];
            }
        }

        return $requirements;
    }
}
