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
    const CURRENT_USER_PLACEHOLDER = 'current';

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

    public function getSubscribedEvents()
    {
        return [
            'onViewsLoad',
        ];
    }

    public function onViewsLoad(GridViewsLoadEvent $event)
    {
        $currentUser = $this->getCurrentUser();
        if (empty($currentUser)) {
            return;
        }

        $gridViews = $event->getGridViews();
        if (empty($gridViews)) {
            return;
        }

        foreach ($gridViews['views'] as &$gridView) {
            if (isset($gridView['filters']['assigneeFullName']) && $gridView['filters']['assigneeFullName'] !== false) {

                if (is_array($gridView['filters']['assigneeFullName'])) {
                    $value = $gridView['filters']['assigneeFullName']['value'];
                } else {
                    $value = $gridView['filters']['assigneeFullName'];
                }

                if (strpos($value, self::FIXTURE_USERNAME) !== false) {
                    $gridView['filters']['assigneeFullName'] = $currentUser->getFirstName() . ' ' . $currentUser->getLastName();
                }
            }

            if (isset($gridView['filters']['watcher'])
                && $gridView['filters']['watcher']['value'] === self::CURRENT_USER_PLACEHOLDER) {

                $gridView['filters']['watcher']['value'] = sprintf("oro_%d", $currentUser->getId());
            }
        }

        $event->setGridViews($gridViews);
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