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

namespace Diamante\ApiBundle\Tests\Routing\Fixtures;

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
     * @param $id
     */
    public function getEntity($id){}

    /**
     * @ApiDoc(
     *  uri="/entities/{id}.{_format}",
     *  method="PUT"
     * )
     * @param $id
     */
    public function putEntity($id){}

    /**
     * @ApiDoc(
     *  uri="/entities/{id}.{_format}",
     *  method={"PUT", "PATCH"}
     * )
     * @param $id
     */
    public function putAndPatchEntity($id){}

    /**
     * @ApiDoc(
     *  uri="/entities.{_format}",
     *  method="POST"
     * )
     * @param $command
     */
    public function postEntity(Command $command){}

    /**
     * @ApiDoc(
     *  uri="/entities/{id}.{_format}",
     *  method="DELETE"
     * )
     * @param $id
     */
    public function deleteEntity($id){}

    /**
     * @ApiDoc(
     *  uri="/entities/{id}/parts.{_format}"
     * )
     * @param $id
     */
    public function getParts($id){}
}
