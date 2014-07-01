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
namespace Eltrino\DiamanteDeskBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Eltrino\DiamanteDeskBundle\Entity\Filter;

class LoadFilterData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $allTicketsFilter = new Filter('All Tickets', 'diamante.ticket.all_tickets_filter_url_generator');
        $manager->persist($allTicketsFilter);

        $myTicketsFilter = new Filter('My Tickets', 'diamante.ticket.my_tickets_filter_url_generator');
        $manager->persist($myTicketsFilter);

        $myOpenTicketsFilter = new Filter('My Open Tickets', 'diamante.ticket.my_open_tickets_filter_url_generator');
        $manager->persist($myOpenTicketsFilter);

        $myReportedFilter = new Filter('My Reported Tickets', 'diamante.ticket.my_reported_tickets_filter_url_generator');
        $manager->persist($myReportedFilter);

        $manager->flush();
    }
}
