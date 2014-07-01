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
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Eltrino\DiamanteDeskBundle\Branch\Model\Logo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Eltrino\DiamanteDeskBundle\Branch\Infrastructure\Persistence\Doctrine\DoctrineBranchRepository")
 * @ORM\Table(name="diamante_branch")
 */
class Branch extends \Eltrino\DiamanteDeskBundle\Branch\Model\Branch
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Branch name
     *
     * @var string $name
     *
     * @ORM\Column(type="string", length=255)
     *
     */
    protected $name;

    /**
     * Branch description
     *
     * @var string $description
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\Logo
     *
     * @ORM\Column(type="branch_logo")
     */
    protected $logo;

    /**
     * @var ArrayCollection
     */
    protected $tags;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    public static function getClassName()
    {
        return __CLASS__;
    }
}
