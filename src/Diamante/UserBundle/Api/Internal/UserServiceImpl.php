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
namespace Diamante\UserBundle\Api\Internal;


use Diamante\DeskBundle\Infrastructure\Notification\NotificationManager;
use Diamante\DeskBundle\Model\Entity\Exception\EntityNotFoundException;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
use Diamante\DeskBundle\Model\Ticket\WatcherListRepository;
use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Api\Command\UpdateDiamanteUserCommand;
use Diamante\UserBundle\Api\GravatarProvider;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Event\UserEvent;
use Diamante\UserBundle\Infrastructure\DiamanteUserFactory;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Diamante\UserBundle\Model\ApiUser\ApiUserRepository;
use Diamante\UserBundle\Model\Exception\DiamanteUserExistsException;
use Diamante\UserBundle\Model\User;
use Diamante\UserBundle\Model\UserDetails;
use Diamante\UserBundle\Entity\ApiUser as ApiUserEntity;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserServiceImpl implements UserService, GravatarProvider
{
    /**
     * @var UserManager
     */
    protected $oroUserManager;

    /**
     * @var DiamanteUserRepository
     */
    protected $diamanteUserRepository;
    /**
     * @var DiamanteUserFactory
     */
    protected $factory;

    /**
     * @var AttachmentManager
     */
    protected $attachmentManager;

    /**
     * @var ApiUserRepository
     */
    protected $diamanteApiUserRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var WatcherListRepository
     */
    private $watcherListRepository;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    public function __construct(
        UserManager $userManager,
        DiamanteUserRepository $diamanteUserRepository,
        DiamanteUserFactory $factory,
        AttachmentManager $attachmentManager,
        ApiUserRepository $diamanteApiUserRepository,
        WatcherListRepository $watcherListRepository,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationService $authorizationService
    ) {
        $this->oroUserManager               = $userManager;
        $this->diamanteUserRepository       = $diamanteUserRepository;
        $this->diamanteApiUserRepository    = $diamanteApiUserRepository;
        $this->factory                      = $factory;
        $this->attachmentManager            = $attachmentManager;
        $this->watcherListRepository        = $watcherListRepository;
        $this->eventDispatcher              = $eventDispatcher;
        $this->authorizationService         = $authorizationService;
    }

    /**
     * @param User $user
     *
     * @return DiamanteUser|OroUser
     */
    public function getByUser(User $user)
    {
        if ($user->isOroUser()) {
            $user = $this->oroUserManager->findUserBy(array('id' => $user->getId()));
        } else {
            $user = $this->diamanteUserRepository->get($user->getId());
        }

        if (!$user) {
            throw new \RuntimeException('User loading failed. User not found');
        }

        return $user;
    }

    /**
     * @param $email
     * @return User|null
     */
    public function getUserByEmail($email)
    {
        $oroUser = $this->oroUserManager->findUserBy(['email' => $email]);
        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($email);

        if ($diamanteUser) {
            return new User($diamanteUser->getId(), User::TYPE_DIAMANTE);
        }

        if ($oroUser) {
            return new User($oroUser->getId(), User::TYPE_ORO);
        }

        return null;
    }

    /**
     * @param $email
     *
     * @return DiamanteUser|OroUser
     */
    public function getUserInstanceByEmail($email)
    {
        $oroUser = $this->oroUserManager->findUserBy(['email' => $email]);

        if ($oroUser) {
            return $oroUser;
        }

        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($email);

        if ($diamanteUser) {
            return $diamanteUser;
        }

        throw new \RuntimeException('User loading failed. User not found');
    }

    /**
     * @param User $user
     *
     * @return bool|OroUser
     */
    public function getOroUser(User $user)
    {
        $user = $this->getByUser($user);

        if ($user instanceof DiamanteUser) {
            $user = $this->oroUserManager->findUserByEmail($user->getEmail());

            if (!$user) {
                return false;
            }
        }

        return $user;
    }

    /**
     * @param User $user
     *
     * @return bool|DiamanteUser
     */
    public function getDiamanteUser(User $user)
    {
        $user = $this->diamanteUserRepository->get($user->getId());

        if (!$user) {
            return false;
        }

        return $user;
    }

    /**
     * @param string $email
     *
     * @return int|null
     */
    public function verifyDiamanteUserExists($email)
    {
        $user = $this->diamanteUserRepository->findUserByEmail($email);

        if (empty($user)) {
            return null;
        }

        return $user->getId();
    }

    /**
     * @param \Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand $command
     *
     * @return int
     */
    public function createDiamanteUser(CreateDiamanteUserCommand $command)
    {
        $user = $this->diamanteUserRepository->findUserByEmail($command->email);

        if (!is_null($user)) {
            if (true === $user->isDeleted()) {
                $this->restoreUser($user);
                return $user->getId();
            } else {
                throw new DiamanteUserExistsException('An account with this email address already exists');
            }
        }

        $user = $this->factory->create(
            $command->email,
            $command->firstName,
            $command->lastName
        );

        $apiUser = new ApiUserEntity($command->email, static::generateRandomSequence(16), static::generateRandomSequence(64), $user);
        $apiUser->generateHash();
        $user->setDeleted(false);
        $user->setApiUser($apiUser);
        $apiUser->setDiamanteUser($user);

        $this->eventDispatcher->dispatch('user.notification', new UserEvent('created', $user));

        $this->diamanteUserRepository->store($user);

        return $user->getId();
    }

    /**
     * @param UpdateDiamanteUserCommand $command
     * @return int
     */
    public function updateDiamanteUser(UpdateDiamanteUserCommand $command)
    {
        /** @var DiamanteUser $user */
        $user = $this->diamanteUserRepository->get($command->id);

        if (is_null($user)) {
            throw new EntityNotFoundException('Failed to load Diamante User, user not found');
        }

        $user->setEmail($command->email);
        $user->setFirstName($command->firstName);
        $user->setLastName($command->lastName);
        $user->getApiUser()->updateEmail($command->email);
        $user->setApiUser($user->getApiUser());
        $user->updateTimestamp();

        $this->diamanteUserRepository->store($user);

        return $user->getId();
    }

    /**
     * @param User $user
     * @return UserDetails
     */
    public function fetchUserDetails(User $user)
    {
        $loadedUser = $this->getByUser($user);

        if (!$loadedUser) {
            throw new \RuntimeException('Failed to load details for given user');
        }

        $userAvatarUrl = null;
        if ($user->getType() == User::TYPE_ORO) {
            if ($loadedUser->getAvatar()) {
                $originalFilename = $loadedUser->getAvatar()->getOriginalFilename();
                if (!empty($originalFilename)) {
                    $userAvatarUrl = $this->attachmentManager->getResizedImageUrl($loadedUser->getAvatar());
                }
            }
        }

        return new UserDetails(
            (string)$user,
            $user->getType(),
            $loadedUser->getEmail(),
            $loadedUser->getFirstName(),
            $loadedUser->getLastName(),
            $userAvatarUrl
        );
    }

    /**
     * @param string $email
     * @param int    $size
     * @param bool   $secure
     *
     * @return string
     */
    public function getGravatarLink($email, $size, $secure = false)
    {
        if (empty($email)) {
            throw new \RuntimeException('No email provided to grab Gravatar for');
        }

        $hash = md5(strtolower(trim($email)));
        $schema = (bool)$secure ? 'https' : 'http';

        //Query parameters are:
        // s - size, default oro avatar size is 58px,
        // d - default image if no configured image found for given hash.
        // for details see https://en.gravatar.com/site/implement/images/

        $link = sprintf('%s://gravatar.com/avatar/%s.jpg?s=%d&d=identicon', $schema, $hash, (int)$size);

        return $link;
    }

    /**
     * @param ApiUser $apiUser
     * @return DiamanteUser
     */
    public function getUserFromApiUser(ApiUser $apiUser)
    {
        return $this->diamanteUserRepository->findUserByEmail(
            $apiUser->getEmail()
        );
    }

    /**
     * @param $id
     */
    public function removeDiamanteUser($id)
    {
        /** @var DiamanteUser $user */
        $user = $this->diamanteUserRepository->get($id);
        $user->setDeleted(true);

        if ($user->getApiUser()) {
            $user->getApiUser()->deactivate();
            $this->diamanteApiUserRepository->store($user->getApiUser());
        }

        $this->diamanteUserRepository->store($user);
        $this->watcherListRepository->removeByUser(new User($user->getId(), User::TYPE_DIAMANTE));
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomSequence($length = 8)
    {
        $charmap = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";

        return substr(str_shuffle($charmap), 0, $length);
    }

    /**
     * @param User $userModel
     */
    public function resetPassword(User $userModel)
    {
        $user = $this->getByUser($userModel);
        $apiUser = $user->getApiUser();

        $apiUser->setPassword($this->generateRandomSequence(16));
        $apiUser->deactivate();

        $apiUser->setDiamanteUser($user);
        $this->diamanteApiUserRepository->store($apiUser);

        $this->eventDispatcher->dispatch('user.notification', new UserEvent('force_reset', $user));
    }

    /**
     * @return string
     */
    public function resolveCurrentUserType()
    {
        $user = $this->authorizationService->getLoggedUser();
        if ($user instanceof ApiUser) {
            $user = $this->getUserFromApiUser($user);
        }

        if ($user instanceof DiamanteUser) {
            return User::TYPE_DIAMANTE;
        }

        return User::TYPE_ORO;
    }

    /**
     * @param DiamanteUser $user
     */
    protected function restoreUser(DiamanteUser $user)
    {
        $user->setDeleted(false);
        $user->updateTimestamp();
        $this->diamanteUserRepository->store($user);

        $this->resetPassword(new User($user->getId(), User::TYPE_DIAMANTE));
    }

}