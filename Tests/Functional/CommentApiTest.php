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
use Diamante\DeskBundle\Model\Ticket\Status;

class CommentApiTest extends ApiTestCase
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

    public function testListComments()
    {
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('GET', 'diamante_comment_api_service_oro_list_all_comments');
    }

    public function testGetComment()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('GET', 'diamante_comment_api_service_oro_load_comment');
    }

    public function testCreateComment()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->command->requestParameters = array(
            'content' => 'Test Comment',
            'ticket' => 1,
            'author' => 'oro_1',
            'ticketStatus' => Status::NEW_ONE
        );
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_CREATED);
        $this->request('POST', 'diamante_comment_api_service_oro_post_new_comment_for_ticket');
    }

    public function testUpdateComment()
    {
        $this->command->urlParameters = array('id' => 2);
        $this->command->requestParameters = array(
            'content' => 'Test Comment Updated PUT',
            'ticketStatus' => Status::CLOSED
        );

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('PUT', 'diamante_comment_api_service_oro_update_comment_content_and_ticket_status');

        $this->command->urlParameters = array('id' => 3);
        $this->command->requestParameters['content'] = 'Test Ticket Updated PATCH';

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('PATCH', 'diamante_comment_api_service_oro_update_comment_content_and_ticket_status');
    }

    public function testDeleteComment()
    {
        $this->command->urlParameters = array('id' => 1);

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_NO_CONTENT);
        $this->request('DELETE', 'diamante_comment_api_service_oro_delete_ticket_comment');

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_NOT_FOUND);
        $this->request('GET', 'diamante_comment_api_service_oro_load_comment');
    }

    public function testAddAttachmentToComment()
    {
        $attachment = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg');
        $this->command->urlParameters = array('commentId' => 1);
        $this->command->requestParameters = array(
            'attachmentsInput' => array(
                array(
                    'filename' => 'test.jpg',
                    'content' => base64_encode($attachment)
                )
            )
        );

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_CREATED);
        $this->request('POST', 'diamante_comment_api_service_oro_add_comment_attachment');
    }

    public function testListCommentAttachment()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('GET', 'diamante_comment_api_service_oro_list_comment_attachment');
    }

    public function testGetCommentAttachment()
    {
        $this->command->urlParameters = array('commentId' => 1, 'attachmentId' => 1);
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('GET', 'diamante_comment_api_service_oro_get_comment_attachment');
    }

    public function testDeleteCommentAttachment()
    {
        $this->command->urlParameters = array('commentId' => 1, 'attachmentId' => 1);

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_NO_CONTENT);
        $this->request('DELETE', 'diamante_comment_api_service_oro_remove_attachment_from_comment');

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_NOT_FOUND);
        $this->request('GET', 'diamante_comment_api_service_oro_get_comment_attachment');
    }

    public function request($method, $uri)
    {
        parent::request($method, $uri, $this->responseAnalyzer, $this->command);
    }
}
