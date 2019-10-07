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
namespace Diamante\DeskBundle\Api\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Diamante\DeskBundle\Model\Branch\Branch;
use Symfony\Component\Validator\Constraints as Assert;
use Diamante\DeskBundle\Validator\Constraints\Any;

class BranchCommand implements Shared\Command
{
    const PERSISTENT_ENTITY = \Diamante\DeskBundle\Entity\Branch::class;

    /**
     * @Assert\Type(type="integer")
     */
    public $id;

    /**
     * @Assert\Regex(
     *    pattern = "/^[\p{L}\p{M}]+$/u",
     *    message = "Key must contain letters only. Numbers, special characters and spaces are not allowed."
     * )
     * @Assert\Type(type="string")
     * @Assert\Length(min = 2)
     * @var string
     */

    public $key;

    /**
     * @Assert\NotNull(
     *              message="This is a required field"
     * )
     * @Assert\Regex(
     *    pattern = "/[\p{L}\p{M}]/u",
     *    message = "Name must contain letters. Only numbers, special characters and spaces are not allowed."
     * )
     * @Assert\Type(type="string")
     * @Assert\Length(min = 2)
     */
    public $name;

    /**
     * @Assert\Type(type="string")
     */
    public $description;

    /**
     * @Assert\File(
     *              mimeTypes={"image/jpeg","image/png"},
     *              mimeTypesMessage="'JPEG' and 'PNG' image formats are supported only"
     * )
     */
    public $logoFile;

    /**
     * @Any({@Assert\Type(type="integer"), @Assert\Type(type="object")})
     */
    public $defaultAssignee;

    /**
     * @Assert\Type(type="object")
     */
    public $logo;

    /**
     * @var $removeLogo
     */
    public $removeLogo;

    public static function fromBranch(Branch $branch)
    {
        $command                  = new self();
        $command->id              = $branch->getId();
        $command->name            = $branch->getName();
        $command->description     = $branch->getDescription();
        $command->defaultAssignee = $branch->getDefaultAssignee();
        $command->logoFile        = null;
        $command->logo            = $branch->getLogo();
        return $command;
    }

    /**
     * @return boolean
     */
    public function isRemoveLogo()
    {
        return $this->removeLogo;
    }
}
