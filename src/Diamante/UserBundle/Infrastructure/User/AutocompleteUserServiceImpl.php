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

use Diamante\AutomationBundle\Rule\Action\AbstractModifyAction;
use Diamante\DeskBundle\Automation\Action\Email\CommentNotifier;
use Diamante\DeskBundle\Automation\Action\Email\TicketNotifier;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\Translation\TranslatorInterface;

class AutocompleteUserServiceImpl implements AutocompleteUserService
{
    const ID_FIELD_NAME = 'id';
    const AVATAR_SIZE = 16;
    const WATCHERS = 'diamante.user.autocomplete.group.watchers';
    const REPORTER = 'diamante.user.autocomplete.group.reporter';
    const ASSIGNEE = 'diamante.user.autocomplete.group.assignee';
    const COMMENT_AUTHOR = 'diamante.user.autocomplete.group.comment_author';
    const DASHED = '------------------';

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
     * @var TranslatorInterface
     */
    protected $translator;

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
     * @param TranslatorInterface    $translator
     * @param array                  $properties
     */
    public function __construct(
        UserManager $userManager,
        DiamanteUserRepository $diamanteUserRepository,
        UserService $userService,
        TranslatorInterface $translator,
        array $properties
    ) {
        $this->oroUserManager = $userManager;
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->userService = $userService;
        $this->translator = $translator;
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
    public function getNotifyActionList()
    {
        $list[CommentNotifier::COMMENT_TYPE] = [
            AbstractModifyAction::PROPERTY_REMOVED => 'User was removed',
            'watchers' => $this->translator->trans(static::WATCHERS),
            'assignee' => $this->translator->trans(static::ASSIGNEE),
            'reporter' => $this->translator->trans(static::REPORTER),
            'author'   => $this->translator->trans(static::COMMENT_AUTHOR)
        ];

        $list[TicketNotifier::TICKET_TYPE] = [
            AbstractModifyAction::PROPERTY_REMOVED => 'User was removed',
            'watchers' => $this->translator->trans(static::WATCHERS),
            'assignee' => $this->translator->trans(static::ASSIGNEE),
            'reporter' => $this->translator->trans(static::REPORTER),
        ];

        $recipientList = [];

        foreach ($this->getUsers() as $user) {
            $recipientList[$user['email']] = $user['firstName'] . ' ' . $user['lastName'] . ' – ' . $user['email'];
        }

        $list[CommentNotifier::COMMENT_TYPE] = array_merge(
            $list[CommentNotifier::COMMENT_TYPE],
            ['null' => static::DASHED],
            $recipientList
        );

        $list[TicketNotifier::TICKET_TYPE] = array_merge(
            $list[TicketNotifier::TICKET_TYPE],
            ['null' => static::DASHED],
            $recipientList
        );

        return $list;
    }

    /**
     * @return array
     */
    protected function getOroUsers()
    {
        $convertedUsers = [];

        $oroUsers = $this->oroUserManager->getRepository()->findAll();

        if (!empty($oroUsers)) {
            $convertedUsers = $this->convertUsers($oroUsers, User::TYPE_ORO);
        }

        return $convertedUsers;
    }

    /**
     * @return array
     */
    public function getAssigners()
    {
        $list = [];

        foreach ($this->getOroUsers() as $key => $user) {
            $list[$key] = $user['firstName'] . ' ' . $user['lastName'] . ' – ' . $user['email'];
        }

        $assigners = array_merge(
            [
                AbstractModifyAction::UNASSIGNED       => 'Unassigned',
                AbstractModifyAction::PROPERTY_REMOVED => 'User was removed'
            ],
            $list
        );

        return $assigners;
    }

    /**
     * @return array
     */
    public function getDiamanteUsers()
    {
        $convertedUsers = [];
        $diamanteUsers = $this->diamanteUserRepository->getAllActiveUsers();

        if (!empty($diamanteUsers)) {
            $convertedUsers = $this->convertUsers($diamanteUsers, User::TYPE_DIAMANTE);
        }

        return $convertedUsers;
    }

    /**
     * @param array $users
     * @param       $type
     *
     * @return array
     */
    protected function convertUsers(array $users, $type)
    {
        $result = [];

        foreach ($users as $user) {
            $converted = [];

            foreach ($this->properties as $property) {
                $converted[$property] = $this->getPropertyValue($property, $user);
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

            $result[$converted['id']] = $converted;
        }

        return $result;
    }

    /**
     * @param string       $name
     * @param object|array $item
     *
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
