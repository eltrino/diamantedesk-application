<?php

namespace Diamante\ApiBundle\Tests\Fixtures;

class Service
{
    /**
     * @api {get} /resources
     */
    private function getList(){}

    /**
     * @api {get} /resources/{id}
     */
    public function getEntity(){}

    /**
     * @api {put} /resources/{id}
     */
    public function putEntity(){}

    /**
     * @api {post} /resources
     */
    public function postEntity(){}

    /**
     * @api {delete} /resources/{id}
     */
    public function deleteEntity(){}

    /**
     * @api {get} /resources/{id}/subresources
     */
    public function getSubEntity(){}
}
