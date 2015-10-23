<?php

namespace Diamante\DistributionBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;

class DefaultSupportDashboard extends AbstractDashboardFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $dashboard = $this->findAdminDashboardModel($manager, 'diamante_support');
        if ($dashboard) {
            $dashboard->setIsDefault(true);
            $mainDashboard = $this->findAdminDashboardModel($manager, 'main');
            if ($mainDashboard) {
                $manager->remove($mainDashboard->getEntity());
            }
        }

        $manager->flush();
    }
}
