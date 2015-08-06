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
namespace Diamante\EmdebbedFormBundle\DataFixtures\Test;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Diamante\DeskBundle\Entity\Branch;

class LoadBranchData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {

        $ASCIIKey = ord('A');
        for ($i = 1; $i <= 10; $i ++) {

            $keySuffix = chr($ASCIIKey + $i);
            $branch = new Branch(
                'BRANCH' . $keySuffix,
                'branchName' . $i,
                'branchDescription' . $i
            );

            $manager->persist($branch);
        }

        $manager->flush();
    }

}
