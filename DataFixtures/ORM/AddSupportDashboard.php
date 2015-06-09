<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;

class AddSupportDashboard extends AbstractDashboardFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $dashboard = $this->findAdminDashboardModel($manager, 'diamante_support');
        if (!$dashboard) {
            $dashboard = $this->createAdminDashboardModel($manager, 'diamante_support');
            $dashboard->setLabel(
                $this->container->get('translator')->trans('diamante.desk.dashboard.support.label')
            );
        }

        $manager->flush();
    }
}
