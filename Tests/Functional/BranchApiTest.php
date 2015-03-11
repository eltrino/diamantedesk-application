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
namespace Diamante\DeskBundle\Tests\Functional;

use Diamante\ApiBundle\Routine\Tests\ApiTestCase;
use Diamante\ApiBundle\Routine\Tests\ResponseAnalyzer;
use Diamante\ApiBundle\Routine\Tests\ApiCommand;
use FOS\Rest\Util\Codes;

class BranchApiTest extends ApiTestCase
{
    /**
     * @var ResponseAnalyzer
     */
    protected $responseAnalyzer;

    /**
     * @var ApiCommand
     */
    protected $command;

    public function setUp()
    {
        parent::setUp();
        $this->responseAnalyzer = new ResponseAnalyzer();
        $this->command = new ApiCommand();
    }

    public function testList()
    {
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('GET', 'diamante_branch_api_service_oro_list_all_branches');
    }

    public function testGetBranch()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('GET', 'diamante_branch_api_service_oro_get_branch');
    }

    public function testCreateBranch()
    {
        $this->command->requestParameters = array(
                "name" => "Test Branch",
                "description" => "Test Description",
                "tags" => array("Test Tag"),
                "key" => "BRANCHTEST"
        );
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_CREATED);
        $this->request('POST', 'diamante_branch_api_service_oro_create_branch');
    }

    public function testUpdateBranch()
    {
        $this->command->urlParameters = array('id' => 2);
        $this->command->requestParameters = array(
            "name" => "Test Branch PUT",
            "description" => "Test Description",
            "tags" => array("Test Tag")
        );

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('PUT', 'diamante_branch_api_service_oro_update_properties');

        $this->command->urlParameters = array('id' => 3);
        $this->command->requestParameters['name'] = 'Test Branch PATCH';

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('PATCH', 'diamante_branch_api_service_oro_update_properties');
    }

    public function testDeleteBranch()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->command->requestParameters = array('branchId' => 1);

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_NO_CONTENT);
        $this->request('DELETE', 'diamante_branch_api_service_oro_delete_branch');

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_NOT_FOUND);
        $this->request('GET', 'diamante_branch_api_service_oro_get_branch');
    }

    public function request($method, $uri)
    {
        parent::request($method, $uri, $this->responseAnalyzer, $this->command);
    }
}
