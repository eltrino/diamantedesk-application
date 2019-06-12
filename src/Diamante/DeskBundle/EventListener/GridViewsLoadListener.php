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

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\EventSubscriber;
use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewRepository;
use Oro\Bundle\DataGridBundle\Event\GridViewsLoadEvent;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GridViewsLoadListener implements EventSubscriber
{

    const FIXTURE_USERNAME = 'fixtureUser';
    const CURRENT_USER_PLACEHOLDER = 'current';

    /** @var Registry */
    protected $registry;

    /** @var SecurityFacade */
    protected $tokenStorage;

    /** @var AclHelper */
    protected $aclHelper;

    /**
     * @param Registry              $registry
     * @param TokenStorageInterface $tokenStorage
     * @param AclHelper             $aclHelper
     */
    public function __construct(
        Registry $registry,
        TokenStorageInterface $tokenStorage,
        AclHelper $aclHelper
    ) {
        $this->registry     = $registry;
        $this->tokenStorage = $tokenStorage;
        $this->aclHelper    = $aclHelper;
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

        foreach ($gridViews as &$gridView) {
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
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();
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
