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
namespace Diamante\DeskBundle\Tests\Functional\Api;

use Diamante\ApiBundle\Routine\Tests\ApiTestCase;
use Diamante\ApiBundle\Routine\Tests\Command\ApiCommand;
use FOS\Rest\Util\Codes;
use Diamante\DeskBundle\Model\Ticket\Status;

class CommentApiTest extends ApiTestCase
{
    /**
     * @var ApiCommand
     */
    protected $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new ApiCommand();
    }

    public function testListComments()
    {
        $this->getAll('diamante_comment_api_service_oro_list_all_comments');
    }

    public function testGetComment()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->get('diamante_comment_api_service_oro_load_comment', $this->command);
    }

    public function testCreateComment()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->command->requestParameters = array(
            'content'      => 'Test Comment',
            'ticket'       => 1,
            'author'       => 'oro_1',
            'ticketStatus' => Status::NEW_ONE
        );
        $this->post('diamante_comment_api_service_oro_post_new_comment_for_ticket', $this->command);
    }

    public function testUpdateComment()
    {
        $this->command->urlParameters = array('id' => 2);
        $this->command->requestParameters = array(
            'content'      => 'Test Comment Updated PUT',
            'ticketStatus' => Status::CLOSED
        );
        $this->put('diamante_comment_api_service_oro_update_comment_content_and_ticket_status', $this->command);

        $this->command->urlParameters = array('id' => 3);
        $this->command->requestParameters['content'] = 'Test Ticket Updated PATCH';
        $this->patch('diamante_comment_api_service_oro_update_comment_content_and_ticket_status', $this->command);
    }

    public function testDeleteComment()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->delete('diamante_comment_api_service_oro_delete_ticket_comment', $this->command);
        $this->get('diamante_comment_api_service_oro_load_comment', $this->command, Codes::HTTP_NOT_FOUND);
    }

    public function testAddAttachmentToComment()
    {
        $file = realpath(dirname(__FILE__) . '/../' . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg');
        $attachment = file_get_contents($file);
        $this->command->urlParameters = array('commentId' => 2);
        $this->command->requestParameters = array(
            'attachmentsInput' => array(
                array(
                    'filename' => 'test.jpg',
                    'content'  => base64_encode($attachment)
                )
            )
        );

        return $this->post('diamante_comment_api_service_oro_add_comment_attachment', $this->command);
    }

    public function testListCommentAttachment()
    {
        $this->command->urlParameters = array('id' => 2);
        $this->get('diamante_comment_api_service_oro_list_comment_attachment', $this->command);
    }

    /**
     * @depends testAddAttachmentToComment
     *
     * @param array $response
     */
    public function testGetCommentAttachment($response)
    {
        $attachmentId = $this->getByKey($response, 'id');
        $this->command->urlParameters = array('commentId' => 2, 'attachmentId' => $attachmentId);
        $this->get('diamante_comment_api_service_oro_get_comment_attachment', $this->command);
    }

    /**
     * @depends testAddAttachmentToComment
     *
     * @param array $response
     */
    public function testDeleteCommentAttachment($response)
    {
        $attachmentId = $this->getByKey($response, 'id');
        $this->command->urlParameters = array('commentId' => 2, 'attachmentId' => $attachmentId);
        $this->delete('diamante_comment_api_service_oro_remove_attachment_from_comment', $this->command);
        $this->get('diamante_comment_api_service_oro_get_comment_attachment', $this->command, Codes::HTTP_NOT_FOUND);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string                                     $key
     */
    protected function getByKey($response, $key)
    {
        return self::jsonToArray($response->getContent())[0][$key];
    }
}