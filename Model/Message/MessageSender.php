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
 
namespace Diamante\EmailProcessingBundle\Model\Message;


class MessageSender
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @param $email
     * @param $name
     */
    public function __construct($email, $name)
    {
        $this->email = $email;

        $this->parseName($name);
    }

    /**
     * @param $name
     */
    protected function parseName($name)
    {
        if (!empty($name)) {
            $name = $this->canonicalizeName($name);

            if (strpos($name," ")) {
                list($firstName, $lastName) = explode(" ", $name);
            } else {
                $firstName = $name;
                $lastName = "";
            }

            $this->firstName = $firstName;
            $this->lastName  = $lastName;
        }
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    public function canonicalizeName($name)
    {
        return str_replace(array('_', ',', '.'), ' ', $name);
    }
}