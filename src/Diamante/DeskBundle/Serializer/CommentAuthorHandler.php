<?php

namespace Diamante\DeskBundle\Serializer;

use Diamante\DeskBundle\Model\Ticket\Comment;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\XmlSerializationVisitor;
use Diamante\UserBundle\Api\UserService;

class CommentAuthorHandler implements SubscribingHandlerInterface
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * CommentAuthorHandler constructor.
     * @param UserService $userService
     */
    public function __construct(
        UserService $userService
    )
    {
        $this->userService = $userService;
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'Diamante\\DeskBundle\\Entity\\Comment',
                'method'    => 'serializeToJson',
            ),
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'Diamante\\DeskBundle\\Entity\\Comment',
                'method'    => 'serializeToXml',
            ),
        );
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param Comment $comment
     * @param array $type
     * @param Context $context
     *
     * @return array|\ArrayObject
     */
    public function serializeToJson(
        JsonSerializationVisitor $visitor,
        Comment $comment,
        array $type,
        Context $context
    )
    {
        $data = $this->getCommentData($comment);
        return $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param XmlSerializationVisitor $visitor
     * @param Comment $comment
     * @param array $type
     * @param Context $context
     */
    public function serializeToXml(
        XmlSerializationVisitor $visitor,
        Comment $comment,
        array $type,
        Context $context
    )
    {
        $data = $this->getCommentData($comment);

        /** @var PropertyMetadata $metadata */
        $metadata = $visitor->getCurrentMetadata();
        $metadata->xmlKeyValuePairs = true;

        $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param Comment $comment
     * @return array
     */
    private function getCommentData(Comment $comment)
    {
        $data = [
            'attachments' => $comment->getAttachments(),
            'content'     => $comment->getContent(),
            'created_at'  => $comment->getCreatedAt(),
            'updated_at'  => $comment->getUpdatedAt(),
            'id'          => $comment->getId(),
            'private'     => $comment->isPrivate(),
            'ticket'      => $comment->getTicketId(),
            'author'      => $this->userService->fetchUserDetails($comment->getAuthor())
        ];

        return $data;
    }
}