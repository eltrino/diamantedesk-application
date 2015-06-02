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


use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Api\GravatarProvider;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Infrastructure\DiamanteUserFactory;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Diamante\UserBundle\Model\User;
use Diamante\UserBundle\Model\UserDetails;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;

class UserServiceImpl implements UserService, GravatarProvider
{
    /**
     * @var UserManager
     */
    private $oroUserManager;

    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;
    /**
     * @var DiamanteUserFactory
     */
    private $factory;

    /**
     * @var AttachmentManager
     */
    protected $attachmentManager;

    function __construct(
        UserManager $userManager,
        DiamanteUserRepository $diamanteUserRepository,
        DiamanteUserFactory $factory,
        AttachmentManager $attachmentManager
    ) {
        $this->oroUserManager         = $userManager;
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->factory                = $factory;
        $this->attachmentManager      = $attachmentManager;
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
     * @param string $email
     *
     * @return bool
     */
    public function isOroUser($email)
    {
        $user = $this->oroUserManager->findUserByEmail($email);

        if(!$user) {
            $user = $this->diamanteUserRepository->findUserByEmail($email);
        }

        if (!$user) {
            throw new \RuntimeException('User loading failed. User with this email doesn\'t exists');
        }

        if ($user instanceof OroUser) {
            return true;
        }

        return false;
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

        if(!$user) {
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
        $user = $this->factory->create(
            $command->username,
            $command->email,
            $command->contact,
            $command->firstName,
            $command->lastName
        );

        $this->diamanteUserRepository->store($user);

        return $user->getId();
    }

    public function fetchUserDetails(User $user)
    {
        $loadedUser = $this->getByUser($user);

        if (!$loadedUser) {
            throw new \RuntimeException('Failed to load details for given user');
        }

        $userAvatarUrl = null;
        $avatar = $loadedUser->getAvatar();
        if ($user->getType() == User::TYPE_ORO && !empty($avatar->getOriginalFilename())) {
            $userAvatarUrl = $this->attachmentManager->getFilteredImageUrl($avatar, 'avatar_med');
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
}