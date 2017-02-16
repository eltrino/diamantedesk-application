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

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\DashboardBundle\Entity\WidgetState;
use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;
use Oro\Bundle\DashboardBundle\Model\DashboardModel;

class AddSupportDashboard extends AbstractDashboardFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * @TODO ORO 2.0 Fix installation
         * Notice: Undefined index: phone in /Volumes/Sites/diamantedesk-application/vendor/doctrine/orm/lib/Doctrine/ORM/Internal/Hydration/AbstractHydrator.php on line 369
         *
         * Unknown column type "" requested. Any Doctrine type that you use has to be registered with \Doctrine\DBAL\Types\Type::addType(). You can get a list of all the known types with
         * \Doctrine\DBAL\Types\Type::getTypesMap(). If this error occurs during database introspection then you might have forgot to register all database types for a Doctrine Type.
         * Use AbstractPlatform#registerDoctrineTypeMapping() or have your custom types implement Type#getMappedDatabaseTypes(). If the type name is empty you might have a problem with
         * the cache or forgot some mapping information.
         *
         */
//        $dashboard = $this->findAdminDashboardModel($manager, 'diamante_support');
//        if (!$dashboard) {
//            $dashboard = $this->createAdminDashboardModel($manager, 'diamante_support');
//            $dashboard->setLabel(
//                $this->container->get('translator')->trans('diamante.desk.dashboard.support.label')
//            );
//        }
//
//        $this->populateDashboard($dashboard, $manager);
//
//        $manager->flush();
    }

    private function populateDashboard(DashboardModel $dashboard, ObjectManager $manager)
    {
        $user = $this->getAdminUser($manager);

        $widgetConfiguration = [
            'ticket_timeline'               => [0, 0],
            'time_of_response_widget'       => [1, 0],
            'tickets_by_channels_widget'    => [0, 1],
            'tickets_by_branches_widget'    => [1, 2],
            'tickets_by_priority_widget'    => [0, 2],
        ];

        foreach ($widgetConfiguration as $name => $position) {
            $widget = new Widget();
            $widget->setDashboard($dashboard->getEntity());
            $widget->setLayoutPosition($position);
            $widget->setName($name);

            $state = new WidgetState();
            $state->setWidget($widget);
            $state->setOwner($user);
            $state->setExpanded(true);

            $manager->persist($widget);
            $manager->persist($state);
        }
    }
}
