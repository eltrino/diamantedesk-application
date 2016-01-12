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
namespace Diamante\DeskBundle\Tests\Loggable;

use Diamante\UserBundle\Entity\DiamanteUser;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Loggable\AuditEntityMapper;

/**
 * Class AuditEntityMapperTest
 *
 * @package Diamante\DeskBundle\Tests\Loggable
 */
class AuditEntityMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\DeskBundle\Loggable\AuditEntityMapper
     */
    private $auditEntityMapper;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->auditEntityMapper = new AuditEntityMapper();
    }

    /**
     * @test
     */
    public function testGetAuditEntryClass()
    {
        $user = $this->getDiamanteUser();

        $this->auditEntityMapper->addAuditEntryClass(
            'Diamante\UserBundle\Entity\DiamanteUser',
            'Diamante\DeskBundle\Entity\Audit'
        );

        $result = $this->auditEntityMapper->getAuditEntryClass($user);

        $this->assertEquals('Diamante\DeskBundle\Entity\Audit', $result);
    }

    /**
     * @test
     */
    public function testGetAuditEntryFieldClass()
    {
        $user = $this->getDiamanteUser();

        $this->auditEntityMapper->addAuditEntryFieldClass(
            'Diamante\UserBundle\Entity\DiamanteUser',
            'Diamante\DeskBundle\Entity\AuditField'
        );

        $result = $this->auditEntityMapper->getAuditEntryFieldClass($user);

        $this->assertEquals('Diamante\DeskBundle\Entity\AuditField', $result);
    }

    /**
     * @return DiamanteUser
     */
    private function getDiamanteUser()
    {
        return new DiamanteUser('dummy@mail.com', 'Mike', 'Bot');
    }
}
