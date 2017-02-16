<?php

namespace Diamante\DeskBundle\Infrastructure\Tag;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TagBundle\Entity\TagManager as OroTagManager;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\Tagging;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\UserBundle\Entity\User;
use Diamante\DeskBundle\Api\Command\Shared\Command;

class TagManager extends OroTagManager
{
    /**
     * Prepare array
     *
     * @param Taggable $entity
     * @param ArrayCollection|null $tags
     * @param Organization $organization
     * @return array
     */
    public function getPreparedArray($entity, $tags = null, Organization $organization = null)
    {
        if (is_null($tags)) {
            $this->loadTagging($entity);
            $tags = $entity->getTags();
        }
        $result = [];

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $entry = [
                'name' => $tag->getName()
            ];
            if (!$tag->getId()) {
                $entry = array_merge(
                    $entry,
                    [
                        'id'    => $tag->getName(),
                        'url'   => false,
                        'owner' => true
                    ]
                );
            } else {
                $entry = array_merge(
                    $entry,
                    [
                        'id'    => $tag->getId(),
                        'url'   => $this->router->generate('oro_tag_search', ['id' => $tag->getId()]),
                        'owner' => false
                    ]
                );
            }

            $taggingCollection = $tag->getTagging()->filter(
                function(Tagging $tagging) use ($entity) {
                    // only use tagging entities that related to current entity
                    if ($entity instanceof Command) {
                        $entityClass = $entity::PERSISTENT_ENTITY;
                    } else {
                        $entityClass = ClassUtils::getClass($entity);
                    }
                    $entitiesMatch = $tagging->getEntityName() == $entityClass;
                    return $entitiesMatch && $tagging->getRecordId() == $entity->getTaggableId();
                }
            );

            /** @var Tagging $tagging */
            foreach ($taggingCollection as $tagging) {
                if ($owner = $tagging->getOwner()) {
                    if ($this->getUser()->getId() == $owner->getId()) {
                        $entry['owner'] = true;
                    }
                }
            }

            $entry['moreOwners'] = $taggingCollection->count() > 1;

            $result[] = $entry;
        }

        return $result;
    }

    /**
     * Return current user
     *
     * @return User
     */
    protected function getUser()
    {
        return $this->securityFacade->getLoggedUser();
    }
}
