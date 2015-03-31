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

namespace Diamante\DeskBundle\Infrastructure\Shared\Authorization;

use Diamante\ApiBundle\Entity\ApiUser;

trait AuthorizationImplTrait
{
    /**
     * @param $attributes
     * @param $object
     *
     * @return bool|mixed
     */
    public function isGranted($attributes, $object)
    {
        if (!is_object($object) && !is_string($object)) {
            return false;
        }

        if (is_object($object)) {
            $objectIdentity = get_class($object);
            $user = $this->securityContext->getToken()->getUser();
            $objectOwner = $object->getOwner();

            if ($user instanceof ApiUser && $attributes == 'EDIT' || $attributes == 'DELETE') {
                if ($objectOwner->isDiamanteUser()) {
                    $ownerId = $this->diamanteUserRepository->findUserByEmail($user->getUserName())->getId();

                    if ($ownerId != $objectOwner->getId()) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }

        if (is_string($object)) {
            $objectIdentity = $object;
        }


        if (array_key_exists($objectIdentity, $this->permissionsMap)) {
            if (in_array($attributes, $this->permissionsMap[$objectIdentity])) {
                return true;
            }
        }

        return false;
    }
}