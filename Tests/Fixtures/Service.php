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

use Diamante\ApiBundle\Annotation\ApiDoc;

class Service
{
    /**
     * @ApiDoc(
     *  uri="/entities",
     *  method="GET"
     * )
     */
    private function getList(){}

    /**
     * @ApiDoc(
     *  uri="/entities/{id}.{_format}",
     *  method="GET"
     * )
     */
    public function getEntity(){}

    /**
     * @ApiDoc(
     *  uri="/entities/{id}.{_format}",
     *  method="PUT"
     * )
     */
    public function putEntity(){}

    /**
     * @ApiDoc(
     *  uri="/entities.{_format}",
     *  method="POST"
     * )
     */
    public function postEntity(){}

    /**
     * @ApiDoc(
     *  uri="/entities/{id}.{_format}",
     *  method="DELETE"
     * )
     */
    public function deleteEntity(){}

    /**
     * @ApiDoc(
     *  uri="/entities/{id}/parts.{_format}"
     * )
     */
    public function getParts(){}
}
