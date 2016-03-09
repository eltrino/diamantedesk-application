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
namespace Diamante\UserBundle\Infrastructure\User;

use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\UserBundle\Entity\UserManager;

class AutocompleteUserServiceImpl implements AutocompleteUserService
{
    const ID_FIELD_NAME = 'id';
    const AVATAR_SIZE   = 16;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var UserManager
     */
    protected $oroUserManager;

    /**
     * @var DiamanteUserRepository
     */
    protected $diamanteUserRepository;

    /**
     * @var array
     */
    protected $properties;

    /**
     * AutocompleteUserServiceImpl constructor.
     *
     * @param UserManager            $userManager
     * @param DiamanteUserRepository $diamanteUserRepository
     * @param UserService            $userService
     * @param array                  $properties
     */
    public function __construct(
        UserManager $userManager,
        DiamanteUserRepository $diamanteUserRepository,
        UserService $userService,
        array $properties
    ) {
        $this->oroUserManager = $userManager;
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->userService = $userService;
        $this->properties = $properties;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        $diamanteUsers = $this->getDiamanteUsers();
        $oroUsers = $this->getOroUsers();

        return array_merge($diamanteUsers, $oroUsers);
    }

    /**
     * @return array
     */
    public function getOroUsers()
    {
        $convertedUsers = [];

        $oroUsers = $this->oroUserManager->findUsers();

        if (!empty($oroUsers)) {
            $convertedUsers = $this->convertUsers($oroUsers, User::TYPE_ORO);
        }

        return $convertedUsers;
    }

    /**
     * @return array
     */
    public function getDiamanteUsers()
    {
        $convertedUsers = [];
        $diamanteUsers = $this->diamanteUserRepository->getAll();

        if (!empty($diamanteUsers)) {
            $convertedUsers = $this->convertUsers($diamanteUsers, User::TYPE_DIAMANTE);
        }

        return $convertedUsers;
    }

    /**
     * @param array $users
     * @param $type
     * @return array
     */
    protected function convertUsers(array $users, $type)
    {
        $result = array();

        foreach ($users as $user) {
            $converted = array();

            foreach($this->properties as $property) {
                $converted[$property]  = $this->getPropertyValue($property, $user);
            }

            $converted['type'] = $type;
            if (is_array($user)) {
                $converted[self::ID_FIELD_NAME] = $type . User::DELIMITER . $user[self::ID_FIELD_NAME];
            } else {
                $converted[self::ID_FIELD_NAME] = $type . User::DELIMITER . $user->getId();
            }

            if ($type === User::TYPE_DIAMANTE) {
                $converted['avatar'] = $this->userService->getGravatarLink($converted['email'], self::AVATAR_SIZE);
                $converted['type_label'] = 'customer';
            } else {
                $converted['avatar'] = $this->getPropertyValue('avatar', $user);
                $converted['type_label'] = 'admin';
            }

            $result[] = $converted;
        }

        return $result;
    }

    /**
     * @param string       $name
     * @param object|array $item
     * @return mixed
     */
    protected function getPropertyValue($name, $item)
    {
        $result = null;

        if (is_object($item)) {
            $method = 'get' . str_replace(' ', '', str_replace('_', ' ', ucwords($name)));
            if (method_exists($item, $method)) {
                $result = $item->$method();
            } elseif (isset($item->$name)) {
                $result = $item->$name;
            }
        } elseif (is_array($item) && array_key_exists($name, $item)) {
            $result = $item[$name];
        }

        return $result;
    }
}
