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
namespace Diamante\DeskBundle\Tests\Functional\Controller;

class BranchWidgetControllerTest extends AbstractController
{
    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    /**
     * @group mass
     */
    public function testDeleteBranchForm()
    {
        $branchFormUrl = $this->getUrl('diamante_branch_view_delete_form', array('id' => 1));
        $crawler = $this->client->request('GET', $branchFormUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Delete", $crawler->selectButton('Delete')->html());
    }
}

