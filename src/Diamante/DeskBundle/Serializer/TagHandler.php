<?php

namespace Diamante\DeskBundle\Serializer;

use Oro\Bundle\TagBundle\Entity\Tag;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\XmlSerializationVisitor;

class TagHandler implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'Oro\\Bundle\\TagBundle\\Entity\\Tag',
                'method'    => 'serializeToJson',
            ),
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'Oro\\Bundle\\TagBundle\\Entity\\Tag',
                'method'    => 'serializeToXml',
            ),
        );
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param Tag                      $tag
     * @param array                    $type
     * @param Context                  $context
     *
     * @return array|\ArrayObject
     */
    public function serializeToJson(
        JsonSerializationVisitor $visitor,
        Tag $tag,
        array $type,
        Context $context
    ) {
        $data = $this->getTagData($tag);

        return $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param XmlSerializationVisitor $visitor
     * @param Tag                     $tag
     * @param array                   $type
     * @param Context                 $context
     */
    public function serializeToXml(
        XmlSerializationVisitor $visitor,
        Tag $tag,
        array $type,
        Context $context
    ) {
        $data = $this->getTagData($tag);

        /** @var PropertyMetadata $metadata */
        $metadata = $visitor->getCurrentMetadata();
        $metadata->xmlKeyValuePairs = true;

        $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param Tag $tag
     *
     * @return array
     */
    private function getTagData(Tag $tag)
    {
        $data = [
            'id'   => $tag->getId(),
            'name' => $tag->getName()
        ];

        return $data;
    }
}