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

use Diamante\DeskBundle\Model\Shared\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository")
 * @ORM\Table(name="diamante_rule_action")
 */
class Action extends \Diamante\AutomationBundle\Model\Action
{
    /**
     * @var \Rhumsaa\Uuid\Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="id")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $parameters;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $weight;

    /**
     * @ORM\ManyToOne(targetEntity="WorkflowRule", inversedBy="actions")
     * @ORM\ManyToOne(targetEntity="BusinessRule", inversedBy="actions")
     */
    protected $rule;
}