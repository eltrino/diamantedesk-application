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
namespace Diamante\DeskBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Oro\Bundle\DataGridBundle\Event\GridViewsLoadEvent;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class GridViewsLoadListener implements EventSubscriber
{

    const FIXTURE_USERNAME = 'fixtureUser';
    /** @var Registry */
    protected $registry;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var AclHelper */
    protected $aclHelper;

    /**
     * @param Registry $registry
     * @param SecurityFacade $securityFacade
     * @param AclHelper $aclHelper
     */
    public function __construct(
        Registry $registry,
        SecurityFacade $securityFacade,
        AclHelper $aclHelper
    ) {
        $this->registry = $registry;
        $this->securityFacade = $securityFacade;
        $this->aclHelper = $aclHelper;
    }

    public function getSubscribedEvents() {
        return [
            'onViewsLoad',
        ];
    }

    public function onViewsLoad(GridViewsLoadEvent $event) {

        $gridName = $event->getGridName();
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return;
        }

        $gridViews = $this->getGridViewRepository()->findGridViews($this->aclHelper, $currentUser, $gridName);
        if (!$gridViews) {
            return;
        }

        foreach ($gridViews as $gridView) {
            $filtersData = $gridView->getFiltersData();

            if(isset($filtersData['assigneeFullName']) && $filtersData['assigneeFullName'] !== false) {
                if(strpos($filtersData['assigneeFullName'], self::FIXTURE_USERNAME) !== false) {
                    $filtersData['assigneeFullName'] = $currentUser->getFirstName() . ' ' . $currentUser->getLastName();
                    $gridView->setFiltersData($filtersData);
                }
            }
        }
    }

    /**
     * @return User
     */
    protected function getCurrentUser()
    {
        $user = $this->securityFacade->getLoggedUser();
        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    /**
     * @return GridViewRepository
     */
    protected function getGridViewRepository()
    {
        return $this->registry->getRepository('OroDataGridBundle:GridView');
    }

}