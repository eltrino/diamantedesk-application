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

namespace Diamante\ApiBundle\Tests\Routing;

use Diamante\ApiBundle\Routing\PropertiesMapper;

class PropertiesMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMap()
    {
        $valid = [
            'id' => '3',
            'priority' => 'low',
            'status' => 'closed'
        ];

        $invalid = [
            'subject' => 'special task'
        ];

        $reflection = new \ReflectionClass('Diamante\ApiBundle\Tests\Routing\Fixtures\Command');
        $mapper = new PropertiesMapper($reflection);
        $object = $mapper->map(array_merge($valid, $invalid));

        foreach ($valid as $key => $value) {
            $this->assertEquals($value, $object->$key);
        }

        foreach ($invalid as $key => $value) {
            $this->assertObjectNotHasAttribute($key, $object);
        }
    }
}
