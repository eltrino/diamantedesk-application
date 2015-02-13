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
namespace Diamante\DeskBundle\Infrastructure\Shared\Authorization;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Diamante\DeskBundle\Model\Shared\Authorization\Authorization;

class OroAuthorization implements Authorization
{
    /**
     * @var SecurityFacade
     */
    private $securityFacade;

    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param $attributes
     * @param null $object
     * @return bool
     */
    public function isGranted($attributes, $object = null)
    {
        return $this->securityFacade->isGranted($attributes, $object);
    }
} 