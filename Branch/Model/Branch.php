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
namespace Eltrino\DiamanteDeskBundle\Branch\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Eltrino\DiamanteDeskBundle\Branch\Model\Logo;
use Oro\Bundle\TagBundle\Entity\Taggable;

class Branch implements Taggable
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\Logo
     */
    protected $logo;

    /**
     * @var ArrayCollection
     */
    protected $tags;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct($name, $description, Logo $logo = null, $tags = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->logo = $logo;
        $this->tags = is_null($tags) ? new ArrayCollection() : $tags;
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return branch logo File
     *
     * @return Logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Update branch
     * @param $name
     * @param $description
     * @param $logo
     */
    public function update($name, $description, Logo $logo = null, $tags = null)
    {
        $this->name = $name;
        $this->description = $description;
        if ($logo) {
            $this->logo = $logo;
        }
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->tags = $tags;
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
     * Returns the collection of tags for this Taggable entity
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTags()
    {
        $this->tags = $this->tags?:new ArrayCollection();
        return $this->tags;
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
} 