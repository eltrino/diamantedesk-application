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

namespace Diamante\UserBundle\Twig\Extensions;

use Diamante\UserBundle\Api\GravatarProvider;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Model\User;
use Diamante\UserBundle\Model\UserDetails;

use Oro\Bundle\UserBundle\Entity\User as OroUser;

class UserDetailsExtension extends \Twig_Extension
{
    /**
     * @var \Diamante\UserBundle\Api\UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
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
            'fetch_user_details' => new \Twig_Function_Method(
                $this,
                'fetchUserDetails',
                array('is_safe' => array('html'))
            ),
            'fetch_oro_user' => new \Twig_Function_Method(
                $this, 'fetchOroUser', array('is_safe' => array('html'))
            ),
            'fetch_diamante_user' => new \Twig_Function_Method(
                $this,
                'fetchDiamanteUser',
                array('is_safe' => array('html'))
            ),
            'get_gravatar' => new \Twig_Function_Method(
                $this,
                'getGravatarForUser',
                array('is_safe' => array('html'))
            ),
        ];
    }

    /**
     * @param User $user
     *
     * @return UserDetails
     *
     * @throws \Twig_Error_Runtime
     */
    public function fetchUserDetails(User $user)
    {
        /**
         * @var UserDetails $details
         */
        $details = $this->userService->fetchUserDetails($user);

        if (empty($details)) {
            throw new \Twig_Error_Runtime('Failed to load user details');
        }

        return $details;
    }

    /**
     * @param User $user
     *
     * @return bool|OroUser
     */
    public function fetchOroUser(User $user)
    {
        return $this->userService->getOroUser($user);
    }

    /**
     * @param User $user
     *
     * @return DiamanteUser
     * @throws \Twig_Error_Runtime
     */
    public function fetchDiamanteUser(User $user)
    {
        return $this->userService->getDiamanteUser($user);
    }

    /**
     * @param string $email
     * @param int    $size
     * @param bool   $secure
     *
     * @throws \Twig_Error_Runtime
     * @return string
     */
    public function getGravatarForUser($email, $size = 58, $secure = false)
    {
        if (!$this->userService instanceof GravatarProvider) {
            throw new \Twig_Error_Runtime('Given user service is not able to provide Gravatar link');
        }

        return $this->userService->getGravatarLink($email, $size, $secure);
    }
}
