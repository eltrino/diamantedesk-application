<?php

namespace Diamante\DeskBundle\Serializer;

use Diamante\DeskBundle\Entity\Attachment;
use Diamante\DeskBundle\Api\Internal\AttachmentServiceImpl;
use Diamante\DeskBundle\Model\Attachment\ManagerImpl;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AttachmentHandler implements SubscribingHandlerInterface
{

    const CREATE_THUMBNAIL_METHOD = 'createThumbnail';

    const URL_PATH_TO_FILE = '/desk/attachments/download/file/';

    const URL_PATH_TO_THUMBNAIL = '/desk/attachments/download/thumbnail/';

    /**
     * @var AttachmentServiceImpl
     */
    protected $attachmentService;

    /**
     * @var ManagerImpl
     */
    protected $managerImpl;

    /**
     * @var ContainerInterface
     */
    private $serviceContainer;


    public function __construct(
        AttachmentServiceImpl $attachmentServiceImpl,
        ManagerImpl $managerImpl,
        ContainerInterface $serviceContainer
    ) {
        $this->attachmentService = $attachmentServiceImpl;
        $this->managerImpl = $managerImpl;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Diamante\\DeskBundle\\Entity\\Attachment',
                'method' => 'serializeToJson',
            ),
        );
    }

    public function serializeToJson(
        JsonSerializationVisitor $visitor,
        Attachment $attachment,
        array $type,
        Context $context
    ) {
        try {
            $thumbnail = $this->attachmentService->getThumbnail($attachment->getHash());
        } catch (FileNotFoundException $e) {
            $thumbnail = null;
        }

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $this->serviceContainer->get('request');

        $data = [
            'id' => $attachment->getId(),
            'created_at' => $attachment->getCreatedAt(),
            'updated_at' => $attachment->getUpdatedAt(),
            'file' => [
                'url' => $request->getUriForPath(static::URL_PATH_TO_FILE . $attachment->getHash()),
                'filename' => $attachment->getFilename()
            ]
        ];

        if ($thumbnail) {
            $data['thumbnails'] = [
                'url' => $request->getUriForPath(static::URL_PATH_TO_THUMBNAIL . $attachment->getHash()),
                'filename' => $thumbnail->getFilename()
            ];
        }

        return $visitor->visitArray($data, $type, $context);
    }

}