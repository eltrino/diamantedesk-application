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
namespace Eltrino\DiamanteDeskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Persistence\Doctrine\DoctrineFilterRepository")
 * @ORM\Table(name="diamante_filter")
 */
class Filter
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="service_id", type="string", length=255, nullable=false)
     */
    private $serviceId;

    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * @param string $name
     * @param string $serviceId
     */
    public function __construct($name, $serviceId)
    {
        $this->name      = $name;
        $this->serviceId = $serviceId;
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
    public function getServiceId()
    {
        return $this->serviceId;
    }
}
