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
namespace Diamante\DeskBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

/**
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Infrastructure\Persistence\DoctrineAuditRepository")
 * @ORM\Table(name="diamante_audit", indexes={
 *  @Index(name="idx_diamante_audit_logged_at", columns={"logged_at"})
 * })
 */
class Audit extends AbstractLogEntry
{
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $action
     *
     * @ORM\Column(type="string", length=8)
     */
    protected $action;

    /**
     * @var string $loggedAt
     *
     * @ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * @var string $objectId
     *
     * @ORM\Column(name="object_id", type="integer", length=32, nullable=true)
     */
    protected $objectId;

    /**
     * @var string $objectClass
     *
     * @ORM\Column(name="object_class", type="string", length=255)
     */
    protected $objectClass;

    /**
     * @var string $objectName
     *
     * @ORM\Column(name="object_name", type="string", length=255)
     */
    protected $objectName;

    /**
     * @var integer $version
     *
     * @ORM\Column(type="integer")
     */
    protected $version;

    /**
     * Redefined parent property to remove the column from db
     *
     * @var array|null
     */
    protected $data;

    /**
     * @var AuditField[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AuditField", mappedBy="audit", cascade={"persist"})
     */
    protected $fields;

    /**
     * @var string $username
     */
    protected $username;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    /**
     * Get object name
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Set object name
     *
     * @param  string $objectName
     * @return Audit
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;

        return $this;
    }

    /**
     * Get fields
     *
     * @return AuditField[]|Collection
     */
    public function getFields()
    {
        if ($this->fields === null) {
            $this->fields = new ArrayCollection();
        }

        return $this->fields;
    }

    /**
     * Get field
     *
     * @param string $field
     *
     * @return AuditField|false
     */
    public function getField($field)
    {
        return $this->fields->filter(function (AuditField $auditField) use ($field) {
            return $auditField->getField() === $field;
        })->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        foreach ($this->getVisibleFields() as $field) {
            $newValue = $field->getNewValue();
            $oldValue = $field->getOldValue();
            if (in_array($field->getDataType(), ['date', 'datetime', 'array', 'jsonarray'])) {
                $newValue = [
                    'value' => $newValue,
                    'type'  => $field->getDataType(),
                ];

                $oldValue = [
                    'value' => $oldValue,
                    'type'  => $field->getDataType(),
                ];
            }

            $data[$field->getField()] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Use method createField instead
     */
    public function setData($data)
    {
        parent::setData($data);
    }

    /**
     * @deprecated This method is for internal use only. Use method getData or getFields instead
     *
     * @return array|null
     */
    public function getDeprecatedData()
    {
        return $this->data;
    }

    /**
     * Create field
     *
     * @param string $field
     * @param string $dataType
     * @param mixed $newValue
     * @param mixed $oldValue
     * @return Audit
     */
    public function createField($field, $dataType, $newValue, $oldValue)
    {
        if ($this->fields === null) {
            $this->fields = new ArrayCollection();
        }

        if ($existingField = $this->getField($field)) {
            $this->fields->removeElement($existingField);
        }

        $auditField = new AuditField($this, $field, $dataType, $newValue, $oldValue);
        $this->fields->add($auditField);

        return $this;
    }

    /**
     * Get visible fields
     *
     * @return AuditField[]|Collection
     */
    protected function getVisibleFields()
    {
        return $this->getFields()->filter(function (AuditField $field) {
            return $field->isVisible();
        });
    }

    public function getAuthor()
    {
        return 'ALEX';
    }

    public function getOrganization()
    {
        return 'diamante';
    }
}
