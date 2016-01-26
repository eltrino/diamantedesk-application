<?php
/*
 * Copyright (c) 2016 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\DataFixtures\ORM;

use Diamante\DeskBundle\Api\Command\BranchCommand;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;

class CreateDefaultBranch extends ContainerAwareFixture
{
    const DEFAULT_BRANCH_NAME = "Default branch";

    public function load(ObjectManager $manager)
    {
        $command = new BranchCommand();
        $command->name = self::DEFAULT_BRANCH_NAME;

        try {
            $branch = $this->container->get('diamante.branch.service')->createBranch($command);
            $manager->persist($branch);
            $manager->flush();
            $manager->clear();

            $this->container->get('oro_config.manager')
                ->set('diamante_desk.default_branch', $branch->getId());

        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')
                ->error("Adding default branch failed. Reason: " . $e->getMessage());
            throw $e;
        }
    }
}