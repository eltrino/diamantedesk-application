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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="diamante_workflow_rule")
 */
class WorkflowRule extends \Diamante\AutomationBundle\Model\WorkflowRule
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
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $status;

    /**
     * @ORM\OneToOne(targetEntity="WorkflowGroup", inversedBy="rule", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="root_group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $grouping;

    /**
     * @ORM\OneToMany(targetEntity="WorkflowAction", mappedBy="rule", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $actions;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $target;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;
}