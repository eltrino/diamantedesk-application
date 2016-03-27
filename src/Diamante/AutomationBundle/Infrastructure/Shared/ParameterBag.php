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

namespace Diamante\AutomationBundle\Infrastructure\Shared;

class ParameterBag implements \IteratorAggregate, \Countable
{
    const CONFIG_PATH_SEPARATOR = '.';

    protected $configParameters;

    public function __construct(array $configParameters = [])
    {
        $this->configParameters = $configParameters;
    }

    /**
     * @return mixed
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->configParameters);
    }

    /**
     * @return mixed
     */
    public function count()
    {
        return count($this->configParameters);
    }

    public function all()
    {
        return $this->configParameters;
    }

    public function get($path)
    {
        $pathParts = explode(self::CONFIG_PATH_SEPARATOR, $path);

        return $this->searchRecursive($pathParts, $this->configParameters);
    }

    public function has($path)
    {
        $needles = explode(self::CONFIG_PATH_SEPARATOR, $path);
        $params = $this->configParameters;

        foreach ($needles as $needle) {
            if (!array_key_exists($needle, $params)) {
                return false;
            }

            $params = &$params[$needle];
        }

        return true;
    }

    public function keys()
    {
        return array_keys($this->configParameters);
    }

    public function set($key, $value)
    {
        $this->configParameters[$key] = $value;
    }

    /**
     * @param $needles
     * @param $haystack
     * @return mixed
     *
     * @TODO: Need to improve the algorithm
     */
    protected function searchRecursive($needles, $haystack)
    {
        $needle = array_shift($needles);

        if (empty($needle)) {
            return $haystack;
        }

        if (!array_key_exists($needle, $haystack)) {
            return null;
        } elseif (is_array($haystack[$needle])) {
            $result = $this->searchRecursive($needles, $haystack[$needle]);
        } else {
            $result = $haystack[$needle];
        }

        return $result;
    }
}