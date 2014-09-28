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
namespace Eltrino\DiamanteDeskBundle\Api\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Eltrino\DiamanteDeskBundle\Model\Branch\Branch;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Symfony\Component\Validator\Constraints as Assert;

class BranchCommand implements Taggable
{
    public $id;

    /**
     * @Assert\NotBlank
     */
    public $name;
    public $description;
    public $tags;
    public $logoFile;
    public $defaultAssignee;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Returns the unique taggable resource identifier
     *
     * @return string
     */
    public function getTaggableId()
    {
        return $this->id;
    }

    /**
     * Set tag collection
     *
     * @param $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Returns the collection of tags for this Taggable entity
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public static function fromBranch(Branch $branch)
    {
        $command                  = new self();
        $command->id              = $branch->getId();
        $command->name            = $branch->getName();
        $command->description     = $branch->getDescription();
        $command->defaultAssignee = $branch->getDefaultAssignee();
        $command->tags            = $branch->getTags();
        $command->logoFile        = null;
        $command->logo            = $branch->getLogo();
        return $command;
    }
}
