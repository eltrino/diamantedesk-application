<?php


namespace Diamante\DeskBundle\Api;

interface AttachmentService
{
    public function getByHash($hash);
}