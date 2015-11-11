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

namespace Diamante\AutomationBundle\Entity;

use Diamante\AutomationBundle\Model\Target;
use Diamante\DeskBundle\Model\Shared\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository")
 * @ORM\Table(name="diamante_business_rule")
 */
class BusinessRule implements Entity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="BusinessCondition", mappedBy="rule")
     */
    protected $conditions;

    /**
     * @ORM\OneToMany(targetEntity="BusinessAction", mappedBy="rule")
     */
    protected $actions;
}