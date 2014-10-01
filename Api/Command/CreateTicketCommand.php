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

class CreateTicketCommand
{
    public $id;

    /**
     * @Assert\NotBlank
     */
    public $branch;

    /**
     * @Assert\NotBlank
     */
    public $subject;

    /**
     * @Assert\NotBlank
     */
    public $description;

    /**
     * @Assert\NotBlank
     */
    public $status;

    /**
     * @var array
     */
    public $files;

    /**
     * @Assert\NotBlank
     */
    public $reporter;

    /**
     * @Assert\NotBlank
     */
    public $assignee;

    /**
     * @Assert\NotBlank
     */
    public $priority;

    /**
     * @Assert\NotBlank
     */
    public $source;

    /**
     * @var \Diamante\DeskBundle\Api\Dto\AttachmentInput
     */
    public $attachmentsInput;
}
