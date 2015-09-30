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
namespace Diamante\DeskBundle\DataFixtures\ORM;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Entity\GridView;

class LoadGridViewData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('Oro\Bundle\UserBundle\Entity\User')->find(1);
        $organization = $manager->getRepository('Oro\Bundle\OrganizationBundle\Entity\Organization')->find(1);

        $newTicketsFilter = new GridView();
        $newTicketsFilter->setOwner($user)
            ->setOrganization($organization)
            ->setName('New tickets')
            ->setType(GridView::TYPE_PUBLIC)
            ->setFiltersData(array('status' => array('value' => array('new'))))
            ->setSortersData(array('assigneeFullName' => '-1'))
            ->setGridName('diamante-ticket-grid');

        $manager->persist($newTicketsFilter);

        $myTicketsFilter = new GridView();
        $myTicketsFilter->setOwner($user)
            ->setOrganization($organization)
            ->setName('My tickets')
            ->setType(GridView::TYPE_PUBLIC)
            ->setFiltersData(array('assigneeFullName' => 'fixtureUser', 'type' => '1'))
            ->setSortersData(array('assigneeFullName' => '-1'))
            ->setGridName('diamante-ticket-grid');

        $manager->persist($myTicketsFilter);

        $myNewTicketsFilter = new GridView();
        $myNewTicketsFilter->setOwner($user)
            ->setOrganization($organization)
            ->setName('My new tickets')
            ->setType(GridView::TYPE_PUBLIC)
            ->setFiltersData(array('assigneeFullName' => 'fixtureUser', 'type' => '1', 'status' => array('value' => array('new'))))
            ->setSortersData(array('assigneeFullName' => '-1'))
            ->setGridName('diamante-ticket-grid');

        $manager->persist($myNewTicketsFilter);

        $myOpenTicketsFilter = new GridView();
        $myOpenTicketsFilter->setOwner($user)
            ->setOrganization($organization)
            ->setName('My open tickets')
            ->setType(GridView::TYPE_PUBLIC)
            ->setFiltersData(array('assigneeFullName' => 'fixtureUser', 'type' => '1', 'status' => array('value' => array('open'))))
            ->setSortersData(array('assigneeFullName' => '-1'))
            ->setGridName('diamante-ticket-grid');

        $manager->persist($myOpenTicketsFilter);
        /*
        $reportedTicketsFilter = new GridView();
        $reportedTicketsFilter->setOwner($user)
            ->setOrganization($organization)
            ->setName('Reported tickets')
            ->setType(GridView::TYPE_PUBLIC)
            ->setFiltersData(array('reporterFullName' => 'fixtureUser', 'type' => '1'))
            ->setSortersData(array('reporterFullName' => '-1'))
            ->setGridName('diamante-ticket-grid');

        $manager->persist($reportedTicketsFilter);

        $reportedNewTicketsFilter = new GridView();
        $reportedNewTicketsFilter->setOwner($user)
            ->setOrganization($organization)
            ->setName('New reported tickets')
            ->setType(GridView::TYPE_PUBLIC)
            ->setFiltersData(array('reporterFullName' => 'fixtureUser', 'type' => '1', 'status' => array('value' => array('new'))))
            ->setSortersData(array('reporterFullName' => '-1'))
            ->setGridName('diamante-ticket-grid');

        $manager->persist($reportedNewTicketsFilter);
        */
        $manager->flush();
    }
}
