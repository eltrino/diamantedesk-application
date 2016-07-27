<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Automation\Action;

use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Rule\Action\AbstractModifyAction;
use Diamante\DeskBundle\Model\Shared\Updatable;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Doctrine\DBAL\LockMode;

/**
 * Class UpdatePropertyAction
 *
 * @package Diamante\AutomationBundle\Automation\Action
 */
class UpdatePropertyAction extends AbstractModifyAction
{
    const ASSIGNEE = 'assignee';
    const UNASSIGNED = 'unassigned';
    const PROPERTY_REMOVED = 'property_removed';
    const ACTION_NAME = 'update_property';

    /**
     * @var AutomationConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var UserService
     */
    private $userService;

    public function execute()
    {
        $context = $this->getContext();
        $target = $context->getFact()->getTarget();
        $targetType = $context->getFact()->getTargetType();
        $properties = $context->getParameters()->all();
        $targetClass = $this->configurationProvider->getEntityConfiguration($targetType)->get('class');
        $targetEntity = new \ReflectionClass($targetClass);

        if (!$targetEntity->hasMethod('updateProperties')) {
            throw new \RuntimeException('Can\'t load entity.');
        }

        if (is_null($target['id'])) {
            return;
        }

        $this->update($target, $targetType, $properties);
    }

    /**
     * @param AutomationConfigurationProvider $configurationProvider
     */
    public function setConfigurationProvider(AutomationConfigurationProvider $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param array $target
     * @param       $targetType
     * @param       $properties
     *
     * @return Updatable
     */
    protected function update(array $target, $targetType, $properties)
    {
        $this->em = $this->getEntityManager();
        $this->em->getConnection()->beginTransaction();

        try {
            $repository = sprintf('DiamanteDeskBundle:%s', ucfirst($targetType));
            $entity = $this->em->find($repository, $target['id'], LockMode::PESSIMISTIC_READ);
            $properties = $this->convertProperties($properties);
            $entity->updateProperties($properties);

            $this->disableListeners();
            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();

        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            $this->update($target, $targetType, $properties);
        }
    }

    /**
     * @param array $properties
     *
     * @return array
     */
    private function convertProperties(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (self::PROPERTY_REMOVED == $value) {
                unset($properties[$name]);
                continue;
            }

            if (static::ASSIGNEE == $name) {
                if (self::UNASSIGNED == $value) {
                    $user = null;
                } else {
                    $user = $this->userService->getByUser(User::fromString($value));
                }

                $properties[$name] = $user;
            }
        }

        return $properties;
    }
}