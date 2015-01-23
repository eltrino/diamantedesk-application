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
namespace Diamante\DeskBundle\Infrastructure\Shared\Adapter;

use Doctrine\ORM\EntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Provider\EmailOwnerProvider;

class DiamanteContactService
{
    /**
     * @var EmailOwnerProvider
     */
    private $emailOwnerProvider;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EmailOwnerProvider $emailOwnerProvider
     * @param EntityManager $entityManager
     */
    public function __construct(EmailOwnerProvider $emailOwnerProvider, EntityManager $entityManager)
    {
        $this->emailOwnerProvider = $emailOwnerProvider;
        $this->entityManager      = $entityManager;
    }

    /**
     * @param $email
     * @return \Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface|\OroCRM\Bundle\ContactBundle\Entity\Contact
     */
    public function findEmailOwner($email)
    {
        return $this->emailOwnerProvider->findEmailOwner($this->entityManager, $email);
    }
}