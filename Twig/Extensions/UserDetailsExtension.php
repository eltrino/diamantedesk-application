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

namespace Diamante\DeskBundle\Twig\Extensions;

use Diamante\DeskBundle\Model\Shared\UserService;
use Diamante\DeskBundle\Model\User\User;
use Diamante\DeskBundle\Model\User\UserDetails;
use Diamante\DeskBundle\Model\User\UserDetailsService;

class UserDetailsExtension extends \Twig_Extension
{
    private $userDetailsService;

    public function __construct(UserDetailsService $userDetailsService, UserService $userService)
    {
        $this->userDetailsService = $userDetailsService;
        $this->userService        = $userService;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'diamante_user_details_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            'fetch_user_details' => new \Twig_Function_Method($this, 'fetchUserDetails', array('is_safe' => array('html'))),
            'fetch_oro_user' => new \Twig_Function_Method($this, 'fetchOroUser', array('is_safe' => array('html'))),
            'get_gravatar' => new \Twig_Function_Method($this, 'getGravatarForUser', array('is_safe' => array('html'))),
        ];
    }

    /**
     * @param User $user
     * @return UserDetails
     * @throws \Twig_Error_Runtime
     */
    public function fetchUserDetails(User $user)
    {
        /**
         * @var \Diamante\DeskBundle\Model\User\UserDetails
         */
        $details = $this->userDetailsService->fetch($user);

        if (empty($details)) {
            throw new \Twig_Error_Runtime('Failed to load user details');
        }

        return $details;
    }

    /**
     * @param User $user
     * @return \Diamante\DeskBundle\Model\User\DiamanteUser|\Oro\Bundle\UserBundle\Entity\User
     * @throws \Twig_Error_Runtime
     */
    public function fetchOroUser(User $user)
    {
        $oroUser = $this->userService->getByUser($user);

        if (empty($oroUser)) {
            throw new \Twig_Error_Runtime('Failed to load user');
        }

        return $oroUser;
    }

    /**
     * @param $userDetails
     * @param int $size
     * @param bool $secure
     * @throws \Twig_Error_Runtime
     * @return string
     */
    public function getGravatarForUser($userDetails, $size = 58, $secure = false)
    {
        if (!($userDetails instanceof User) && !($userDetails instanceof UserDetails)) {
            throw new \Twig_Error_Runtime(
                sprintf('Invalid user details source is provided. Expected instance of %s, %s given',
                    join(' or ', [
                        'Diamante\DeskBundle\Model\User\User',
                        'Diamante\DeskBundle\Model\User\UserDetails',
                    ]),
                    get_class($userDetails)
                )
            );
        }

        if (is_object($userDetails) && ($userDetails instanceof User)) {
            $userDetails = $this->userDetailsService->fetch($userDetails);
        }

        $email = $userDetails->getEmail();
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
