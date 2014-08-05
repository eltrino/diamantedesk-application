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
namespace Eltrino\DiamanteDeskBundle\EmailProcessing\Model;

class Message
{
    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var string
     */
    private $content;

    public function __construct($uniqueId, $content)
    {
        $this->uniqueId = $uniqueId;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
