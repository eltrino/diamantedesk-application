<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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
 * @ORM\Entity
 * @ORM\Table(name="diamante_automation_context")
 */
class PersistentProcessingContext extends \Diamante\AutomationBundle\Model\PersistentProcessingContext
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $targetEntityId;

    /**
     * @ORM\Column(type="string")
     */
    protected $targetEntityClass;

    /**
     * @ORM\Column(type="array")
     */
    protected $targetEntityChangeset;

    /**
     * @ORM\Column(type="string")
     */
    protected $state;
}