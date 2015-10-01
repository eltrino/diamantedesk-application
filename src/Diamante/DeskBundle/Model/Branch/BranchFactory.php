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
namespace Diamante\DeskBundle\Model\Branch;

use Diamante\DeskBundle\Model\Shared\AbstractEntityFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\User;

class BranchFactory extends AbstractEntityFactory
{
    /**
     * @var BranchKeyGenerator
     */
    private $branchKeyGenerator;

    public function __construct($entityClassName, BranchKeyGenerator $branchKeyGenerator)
    {
        parent::__construct($entityClassName);
        $this->branchKeyGenerator = $branchKeyGenerator;
    }

    /**
     * Create Branch
     *
     * @param string                        $name
     * @param string                        $description
     * @param null|string                   $key
     * @param null|User                     $defaultAssignee
     * @param Logo|null|\SplFileInfo        $logo
     * @param null|array|ArrayCollection    $tags
     * @return Branch
     */
    public function create(
        $name,
        $description,
        $key = null,
        User $defaultAssignee = null,
        Logo $logo = null,
        $tags = null
    ) {
        if (is_null($key) || empty($key)) {
            $key = $this->branchKeyGenerator->generate($name);
        }

        return new $this->entityClassName($key, $name, $description, $defaultAssignee, $logo, $tags);
    }
}
