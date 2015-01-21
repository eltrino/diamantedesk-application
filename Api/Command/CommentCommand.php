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

use Symfony\Component\Validator\Constraints as Assert;

class CommentCommand
{
    /**
     * @Assert\Type(type="integer")
     */
    public $id;

    /**
     * @Assert\NotNull(
     *              message="This is a required field"
     * )
     * @Assert\Type(type="string")
     */
    public $content;

    /**
     * @Assert\Type(type="object")
     */
    public $attachmentList;

    /**
     * @var array
     * @Assert\NotNull()
     * @Assert\Type(type="array")
     */
    public $attachmentsInput;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="int")
     * @var int
     */
    public $ticket;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="string")
     * @var string
     */
    public $author;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="string")
     */
    public $ticketStatus;
}
