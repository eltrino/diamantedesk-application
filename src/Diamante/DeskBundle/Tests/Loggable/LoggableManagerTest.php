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

use Diamante\DeskBundle\Loggable\LoggableManager;
use Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

/**
 * Class LoggableManagerTest
 *
 * @package Diamante\DeskBundle\Tests\Loggable
 */
class LoggableManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggableManager
     */
    protected $loggableManager;

    /**
     * @var \Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink
     * @Mock Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink
     */
    private $securityContextLink;

    /**
     * @var \Diamante\DeskBundle\Loggable\AuditEntityMapper
     * @Mock Diamante\DeskBundle\Loggable\AuditEntityMapper
     */
    private $auditEntityMapper;

    /**
     * @var \Oro\Bundle\DataAuditBundle\Loggable\AuditEntityMapper
     * @Mock Oro\Bundle\DataAuditBundle\Loggable\AuditEntityMapper
     */
    private $oroAuditEntityMapper;

    /**
     * @var \Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider
     * @Mock Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider
     */
    private $provider;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     * @Mock Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $em;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->auditEntityMapper->addAuditEntryClass(
            'Diamante\UserBundle\Entity\DiamanteUser',
            'Diamante\DeskBundle\Entity\Audit'
        );

        $this->auditEntityMapper->addAuditEntryFieldClass(
            'Diamante\UserBundle\Entity\DiamanteUser',
            'Diamante\DeskBundle\Entity\AuditField'
        );

        $this->container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->oroAuditEntityMapper))
            ->with($this->equalTo('oro_dataaudit.loggable.audit_entity_mapper'));

        $this->loggableManager = new LoggableManager(
            'Oro\Bundle\DataAuditBundle\Entity\Audit',
            'Oro\Bundle\DataAuditBundle\Entity\AuditField',
            $this->provider,
            $this->securityContextLink,
            $this->auditEntityMapper,
            $this->container
        );
    }

    /**
     * @test
     */
    public function testHandlePostPersist()
    {
        $loggableClass = new LoggableClass();
        $loggableClass->setName('testName');
        $this->loggableManager->handlePostPersist($loggableClass, $this->em);
    }
}
