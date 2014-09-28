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
namespace Eltrino\DiamanteDeskBundle\Model\Branch;

class Logo
{
    const PATH_TO_LOGO_DIR = '/uploads/branch/logo/';

    /**
     * @var
     */
    private $name;

    /**
     * @var string
     */
    private $pathname;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name     = $name;
        $this->pathname = self::PATH_TO_LOGO_DIR . $name;
    }

    /**
     * Returns pathname of logo
     * @return string
     */
    public function getPathname()
    {
        return $this->pathname;
    }

    /**
     * Returns filename with extension
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
