<?php


namespace Diamante\UserBundle\Api;


interface GravatarProvider
{
    public function getGravatarLink($email, $size, $secure = false);
}