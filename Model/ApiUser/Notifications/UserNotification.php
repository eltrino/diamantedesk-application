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
namespace Diamante\UserBundle\Model\ApiUser\Notifications;

use Diamante\DeskBundle\Model\Shared\Notification;

class UserNotification implements Notification
{
    /**
     * @var string
     */
    private $headerText;

    /**
     * @var \Diamante\UserBundle\Entity\ApiUser
     */
    private $author;

    public function __construct($author, $headerText) {
        $this->headerText = $headerText;
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return $this->headerText;
    }

    /**
     * @return \Diamante\UserBundle\Entity\ApiUser
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
