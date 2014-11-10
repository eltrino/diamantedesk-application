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
