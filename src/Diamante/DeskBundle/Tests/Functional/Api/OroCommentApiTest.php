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
use FOS\RestBundle\Util\Codes;
use Diamante\DeskBundle\Model\Ticket\Status;

class OroCommentApiTest extends ApiTestCase
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

    /**
     * @return array
     */
    public function testCreateComment()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->command->requestParameters = array(
            'content'      => 'Test Comment',
            'ticket'       => 1,
            'author'       => 'oro_1',
            'ticketStatus' => Status::NEW_ONE
        );
        $response = $this->post('diamante_comment_api_service_oro_post_new_comment_for_ticket', $this->command);

        return $this->getArray($response);
    }

    public function testListComments()
    {
        $this->getAll('diamante_comment_api_service_oro_list_all_comments');
    }

    /**
     * @depends testCreateComment
     *
     * @param array $comment
     */
    public function testGetComment($comment)
    {
        $this->command->urlParameters = array('id' => $comment['id']);
        $this->get('diamante_comment_api_service_oro_load_comment', $this->command);
    }

    /**
     * @depends testCreateComment
     *
     * @param array $comment
     */
    public function testUpdateComment($comment)
    {
        $this->command->urlParameters = array('id' => $comment['id']);
        $this->command->requestParameters = array(
            'content'      => 'Test Comment Updated PUT',
            'ticketStatus' => Status::CLOSED
        );
        $this->put('diamante_comment_api_service_oro_update_comment_content_and_ticket_status', $this->command);

        $this->command->urlParameters = array('id' => $comment['id']);
        $this->command->requestParameters['content'] = 'Test Ticket Updated PATCH';
        $this->patch('diamante_comment_api_service_oro_update_comment_content_and_ticket_status', $this->command);
    }

    /**
     * @depends testCreateComment
     *
     * @param array $comment
     *
     * @return array
     */
    public function testDeleteComment($comment)
    {
        $this->command->urlParameters = array('id' => $comment['id']);
        $this->delete('diamante_comment_api_service_oro_delete_ticket_comment', $this->command);
        $this->get('diamante_comment_api_service_oro_load_comment', $this->command, Codes::HTTP_NOT_FOUND);

        return $this->testCreateComment();
    }

    /**
     * @depends testDeleteComment
     *
     * @param array $comment
     *
     * @return array
     */
    public function testAddAttachmentToComment($comment)
    {
        $file = realpath(
            dirname(__FILE__) . '/../' . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg'
        );
        $attachment = file_get_contents($file);
        $this->command->urlParameters = array('commentId' => $comment['id']);
        $this->command->requestParameters = array(
            'attachmentsInput' => array(
                array(
                    'filename' => 'test.jpg',
                    'content'  => base64_encode($attachment)
                )
            )
        );

        $response = $this->post('diamante_comment_api_service_oro_add_comment_attachment', $this->command);

        return $this->getArray($response);
    }

    /**
     * @depends testDeleteComment
     *
     * @param array $comment
     */
    public function testListCommentAttachment($comment)
    {
        $this->command->urlParameters = array('id' => $comment['id']);
        $this->get('diamante_comment_api_service_oro_list_comment_attachment', $this->command);
    }

    /**
     * @depends testDeleteComment
     * @depends testAddAttachmentToComment
     *
     * @param $comment
     * @param $attachments
     */
    public function testGetCommentAttachment($comment, $attachments)
    {
        $this->command->urlParameters = array('commentId' => $comment['id'], 'attachmentId' => $attachments[0]['id']);
        $this->get('diamante_comment_api_service_oro_get_comment_attachment', $this->command);
    }

    /**
     * @depends testDeleteComment
     * @depends testAddAttachmentToComment
     *
     * @param $comment
     * @param $attachments
     */
    public function testDeleteCommentAttachment($comment, $attachments)
    {
        $this->command->urlParameters = array('commentId' => $comment['id'], 'attachmentId' => $attachments[0]['id']);
        $this->delete('diamante_comment_api_service_oro_remove_attachment_from_comment', $this->command);
        $this->get('diamante_comment_api_service_oro_get_comment_attachment', $this->command, Codes::HTTP_NOT_FOUND);
    }
}