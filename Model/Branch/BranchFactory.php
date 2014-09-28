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

use Eltrino\DiamanteDeskBundle\Model\Branch\Logo;
use Eltrino\DiamanteDeskBundle\Model\Shared\AbstractEntityFactory;
use Oro\Bundle\UserBundle\Entity\User;

class BranchFactory extends AbstractEntityFactory
{
    /**
     * Create Branch
     *
     * @param string $name
     * @param string $description
     * @param null|User $defaultAssignee
     * @param null|\SplFileInfo $logo
     * @param null|array $tags
     * @return Branch
     */
    public function create($name, $description, User $defaultAssignee = null, \SplFileInfo $logo = null, $tags = null)
    {
        if ($logo) {
            $logo = new Logo($logo->getFilename());
        }
        return new $this->entityClassName($name, $description, $defaultAssignee, $logo, $tags);
    }
}
