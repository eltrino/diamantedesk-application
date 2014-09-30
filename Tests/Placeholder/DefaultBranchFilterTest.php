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
namespace Eltrino\DiamanteDeskBundle\Tests\Placeholder;

use Eltrino\DiamanteDeskBundle\Placeholder\DefaultBranchFilter;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class DefaultBranchFilterTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_BRANCH_ID = 8;

    /**
     * @var DefaultBranchFilter
     */
    private $filter;

    /**
     * @var \Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings
     * @Mock \Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings
     */
    private $systemSettings;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->systemSettings->expects($this->any())->method('getDefaultBranchId')
            ->will($this->returnValue(self::DEFAULT_BRANCH_ID));
        $this->filter = new DefaultBranchFilter($this->systemSettings);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function filter(array $items, array $variables, $expectedCount)
    {
        $result = $this->filter->filter($items, $variables);

        $this->assertCount($expectedCount, $result);
        if ($expectedCount) {
            $this->assertEquals($items, $result);
        }
    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    array(
                        'template' => 'template.html.twig',
                        'default_branch' => array('entity'),
                        'name' => 'default_branch_notification'
                    )
                ),
                array(
                    'entity' => new Branch(8, 'DUMMY_NAME', 'DUMMY_DESC')
                ),
                1
            ),
            array(
                array(
                    array(
                        'template' => 'template.html.twig',
                        'default_branch' => array('entity'),
                        'name' => 'default_branch_notification'
                    )
                ),
                array(
                    'entity' => new Branch(9, 'DUMMY_NAME', 'DUMMY_DESC')
                ),
                0
            ),
            array(
                array(
                    array(
                        'template' => 'template.html.twig',
                        'default_branch' => null,
                        'name' => 'default_branch_notification'
                    )
                ),
                array(
                    'entity' => new Branch(4, 'DUMMY_NAME', 'DUMMY_DESC')
                ),
                1
            ),
            array(
                array(
                    array(
                        'template' => 'template.html.twig',
                        'default_branch' => array('entity'),
                        'name' => 'default_branch_notification'
                    )
                ),
                array(
                    'branch' => new Branch(9, 'DUMMY_NAME', 'DUMMY_DESC')
                ),
                0
            ),
            array(
                array(
                    array(
                        'template' => 'template.html.twig',
                        'default_branch' => array('entity'),
                        'name' => 'default_branch_notification'
                    )
                ),
                array(
                    'entity' => new \StdClass()
                ),
                0
            )
        );
    }
}
