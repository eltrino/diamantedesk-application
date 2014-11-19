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

namespace Diamante\ApiBundle\Tests;

use Diamante\ApiBundle\Routing\RestServiceLoader;
use Diamante\ApiBundle\Tests\Fixtures\Service;
use Doctrine\Common\Annotations\AnnotationReader;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class RestServiceLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     * @Mock \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    /** @var  \Diamante\ApiBundle\Routing\RestServiceLoader */
    private $loader;

    protected function setUp()
    {
        // force loading for annotation
        class_exists('Diamante\ApiBundle\Annotation\ApiDoc');

        MockAnnotations::init($this);
        $this->reader = new AnnotationReader();
        $this->loader = new RestServiceLoader($this->container, $this->reader);
    }

    public function testAnnotatedMethods()
    {
        $this->container->expects($this->once())->method('get')->with($this->equalTo('fixture.service'))
            ->will($this->returnValue(new Service()));

        $collection = $this->loader->load('fixture.service', 'diamante_rest_service');

        $this->assertEquals(5, $collection->count());

        $this->assertEquals('GET', $collection->get('fixture_service_get_entity')->getRequirement('_method'));
        $this->assertEquals('fixture.service:getEntity', $collection->get('fixture_service_get_entity')->getDefault('_controller'));
        $this->assertTrue($collection->get('fixture_service_get_entity')->getDefault('_diamante_api'));

        $this->assertEquals('PUT', $collection->get('fixture_service_put_entity')->getRequirement('_method'));
        $this->assertEquals('POST', $collection->get('fixture_service_post_entity')->getRequirement('_method'));
        $this->assertEquals('DELETE', $collection->get('fixture_service_delete_entity')->getRequirement('_method'));
        $this->assertEquals('ANY', $collection->get('fixture_service_get_parts')->getRequirement('_method'));
    }
}
