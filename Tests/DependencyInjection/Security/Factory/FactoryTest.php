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
namespace Diamante\ApiBundle\Tests\DependencyInjection\Security\Factory;

use Diamante\ApiBundle\DependencyInjection\Security\Factory\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\ApiBundle\DependencyInjection\Security\Factory\Factory
     */
    private $factory;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->factory = new Factory();
    }

    /**
     * @test
     */
    public function getPosition()
    {
        $result = $this->factory->getPosition();
        $this->assertEquals('pre_auth', $result);
    }

    /**
     * @test
     */
    public function getKey()
    {
        $result = $this->factory->getKey();
        $this->assertEquals('wsse_diamante_api', $result);
    }

    protected function getFactory()
    {
        return $this->getMockForAbstractClass('Escape\WSSEAuthenticationBundle\DependencyInjection\Security\Factory\Factory', array());
    }

    public function testCreate()
    {
        $container = new ContainerBuilder();

        list($authProviderId, $listenerId, $defaultEntryPoint) = $this->factory
            ->create(
                $container,
                'foo',
                array(
                ),
                'user_provider',
                'entry_point'
            );

        $this->assertEquals('security.authentication.provider.wsse.foo', $authProviderId);
        $this->assertTrue($container->hasDefinition('security.authentication.provider.wsse.foo'));

        $this->assertEquals('security.authentication.listener.wsse.foo', $listenerId);
        $this->assertTrue($container->hasDefinition('security.authentication.listener.wsse.foo'));
    }
}
