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
namespace Diamante\EmbeddedFormBundle\Entity;

use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm as BaseEmbeddedForm;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class EmbeddedForm extends BaseEmbeddedForm
{

    /**
     * @var integer
     *
     * @ORM\Column(name="branch_id", type="integer")
     */
    protected $branch;


    /**
     * @param integer $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    /**
     * @return integer
     */
    public function getBranch()
    {
        return $this->branch;
    }
}
