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
namespace Diamante\DeskBundle\DataFixtures\Test;

use Diamante\DeskBundle\DataFixtures\AbstractContainerAwareFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\TagBundle\Entity\Tag;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

class LoadTagData extends AbstractContainerAwareFixture
{
    /** @var EntityRepository */
    private $organizationRepository;

    public function load(ObjectManager $manager)
    {
        $organization = current($this->organizationRepository->getEnabled());
        for ($i = 1; $i <= 10; $i++) {
            $tag = new Tag('tag ' . $i);
            $tag->setOrganization($organization);

            $manager->persist($tag);
        }

        $manager->flush();
    }

    /**
     * @return null
     */
    protected function init()
    {
        /** @var  EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine')->getManager();
        $this->organizationRepository = $entityManager->getRepository('OroOrganizationBundle:Organization');
    }
}
