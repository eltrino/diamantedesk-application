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
namespace Diamante\DeskBundle\Tests\Infrastructure\Shared\Adapter;

use Diamante\DeskBundle\Infrastructure\Shared\Adapter\DiamanteContactService;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class DiamanteContactServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \OroCRM\Bundle\ContactBundle\Entity\Provider\EmailOwnerProvider
     * @Mock \OroCRM\Bundle\ContactBundle\Entity\Provider\EmailOwnerProvider
     */
    private $emailOwnerProvider;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var DiamanteContactService
     */
    private $diamanteContactService;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->diamanteContactService = new DiamanteContactService($this->emailOwnerProvider, $this->entityManager);
    }

    public function testFindEmailOwner()
    {
        $email = 'test@gmail.com';
        $this->emailOwnerProvider
            ->expects($this->once())
            ->method('findEmailOwner')
            ->with(
                $this->entityManager, $email
            );
        $this->diamanteContactService->findEmailOwner($email);
    }
}