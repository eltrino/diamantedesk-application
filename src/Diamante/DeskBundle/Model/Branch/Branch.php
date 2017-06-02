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
namespace Diamante\DeskBundle\Model\Branch;

use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Shared\Property;
use Oro\Bundle\UserBundle\Entity\User;

class Branch implements Entity, Property
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
     * Branch key. Immutable value - generated only once when new Branch created
     * @var string
     */
    protected $key;

    /**
     * @var User
     */
    protected $defaultAssignee;

    /**
     * @var Logo
     */
    protected $logo;

    /**
     * @var integer
     */
    protected $sequenceNumber;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct(
        $key,
        $name,
        $description,
        User $defaultAssignee = null,
        Logo $logo = null
    ) {
        $this->key = $key;
        $this->name = $name;
        $this->description = $description;
        $this->defaultAssignee = $defaultAssignee;
        $this->logo = $logo;
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
     * @return int
     */
    public function getValue()
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
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return User
     */
    public function getDefaultAssignee()
    {
        return $this->defaultAssignee;
    }

    /**
     * @return int|null
     */
    public function getDefaultAssigneeId()
    {
        if (is_null($this->defaultAssignee)) {
            return null;
        }
        return $this->defaultAssignee->getId();
    }

    /**
     * @return string|null
     */
    public function getDefaultAssigneeFullName()
    {
        if (is_null($this->defaultAssignee)) {
            return null;
        }
        return $this->defaultAssignee->getFirstName() . ' ' . $this->defaultAssignee->getLastName();
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
     * @param null|User $defaultAssignee
     * @param null|Logo $logo
     * @return void
     */
    public function update($name, $description, User $defaultAssignee = null, Logo $logo = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->defaultAssignee = $defaultAssignee;
        if ($logo !== null) {
            $this->logo = $logo;
        }
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    public function setSequenceNumber($sequenceNumber)
    {
        $this->sequenceNumber = $sequenceNumber;

        return $this;
    }

    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * Update single property of the branch
     *
     * @param $name
     * @param $value
     * @return void
     */
    public function updateProperty($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            throw new \DomainException(sprintf('Branch does not have "%s" property.', $name));
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
