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
namespace Eltrino\DiamanteDeskBundle\Branch\Model\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Branch\Model\Logo;

class BranchFactory
{
    /**
     * Create Branch
     * @param $name
     * @param $description
     * @param \SplFileInfo $logo
     * @return Branch
     */
    public function create($name, $description, \SplFileInfo $logo = null, $tags = null)
    {
        if ($logo) {
            $logo = new Logo($logo->getFilename());
        }
        return new Branch($name, $description, $logo, $tags);
    }
}
