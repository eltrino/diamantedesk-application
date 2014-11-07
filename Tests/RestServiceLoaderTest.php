<?php

namespace Diamante\ApiBundle\Tests;

use Diamante\ApiBundle\Routing\RestServiceLoader;
use Diamante\ApiBundle\Tests\Fixtures\Service;

class RestServiceLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testAnnotatedMethods()
    {

        $loader = $this->getLoader();
        $collection = $loader->load('fixture.service', 'rest_service');

        $this->assertEquals(5, $collection->count());

        $this->assertEquals('GET', $collection->get('fixture_service_get_entity')->getRequirement('_method'));
        $this->assertEquals('fixture.service', $collection->get('fixture_service_get_entity')->getDefault('_service_id'));
        $this->assertEquals('getEntity', $collection->get('fixture_service_get_entity')->getDefault('_service_method'));
        $this->assertEquals('DiamanteApiBundle:Index:index', $collection->get('fixture_service_get_entity')->getDefault('_controller'));

        $this->assertEquals('PUT', $collection->get('fixture_service_put_entity')->getRequirement('_method'));
        $this->assertEquals('POST', $collection->get('fixture_service_post_entity')->getRequirement('_method'));
        $this->assertEquals('DELETE', $collection->get('fixture_service_delete_entity')->getRequirement('_method'));
        $this->assertEquals('GET', $collection->get('fixture_service_get_sub_entity')->getRequirement('_method'));
    }

    private function getLoader()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->once())->method('get')->with($this->equalTo('fixture.service'))
            ->will($this->returnValue(new Service()));

        return new RestServiceLoader($container);
    }
}
