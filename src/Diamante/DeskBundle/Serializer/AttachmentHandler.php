<?php

namespace Diamante\DeskBundle\Serializer;

use Diamante\DeskBundle\Api\Internal\AttachmentServiceImpl;
use Diamante\DeskBundle\Entity\Attachment;
use Diamante\DeskBundle\Model\Attachment\ManagerImpl;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\XmlSerializationVisitor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

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
                'format'    => 'json',
                'type'      => Attachment::class,
                'method'    => 'serializeToJson',
            ),
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => Attachment::class,
                'method'    => 'serializeToXml',
            ),
        );
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param Attachment $attachment
     * @param array $type
     * @param Context $context
     *
     * @return array|\ArrayObject|mixed
     */
    public function serializeToJson(
        JsonSerializationVisitor $visitor,
        Attachment $attachment,
        array $type,
        Context $context
    ) {
        $data = $this->getAttachmentData($attachment);

        return $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param XmlSerializationVisitor $visitor
     * @param Attachment $attachment
     * @param array $type
     * @param Context $context
     *
     * @return array|\ArrayObject|mixed
     */
    public function serializeToXml(
        XmlSerializationVisitor $visitor,
        Attachment $attachment,
        array $type,
        Context $context
    ) {
        $data = $this->getAttachmentData($attachment);

        /** @var PropertyMetadata $metadata */
        $metadata = $visitor->getCurrentMetadata();
        $metadata->xmlKeyValuePairs = true;

        return $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param Attachment $attachment
     * @return array
     */
    private function getAttachmentData(Attachment $attachment)
    {
        try {
            $thumbnail = $this->attachmentService->getThumbnail($attachment->getHash());
        } catch (FileNotFoundException $e) {
            // try to generate thumbnail on the fly
            $thumbnail = $this->createThumbnail($attachment);
        }

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $this->serviceContainer->get('request');

        $data = [
            'id'         => $attachment->getId(),
            'created_at' => $attachment->getCreatedAt(),
            'updated_at' => $attachment->getUpdatedAt(),
            'file'       => [
                'url'      => $request->getUriForPath(static::URL_PATH_TO_FILE . $attachment->getHash()),
                'filename' => $attachment->getFilename(),
            ],
        ];

        if ($thumbnail) {
            $data['thumbnails'] = [
                'url'      => $request->getUriForPath(static::URL_PATH_TO_THUMBNAIL . $attachment->getHash()),
                'filename' => $thumbnail->getFilename(),
            ];
        }

        return $data;
    }

    /**
     * @param Attachment $attachment
     * @return null|\Symfony\Component\HttpFoundation\File\File
     */
    private function createThumbnail(Attachment $attachment)
    {
        $fileName = $attachment->getFile()->getPathname();
        $fileParts = explode('/', $fileName);
        array_pop($fileParts);
        $prefix = array_pop($fileParts);

        try {
            $this->managerImpl->createThumbnail($attachment->getFile(), $attachment->getHash(), $prefix);
            $thumbnail = $this->attachmentService->getThumbnail($attachment->getHash());
        } catch (\Exception $e) {
            $thumbnail = null;
        }

        return $thumbnail;
    }

}