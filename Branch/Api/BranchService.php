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
namespace Eltrino\DiamanteDeskBundle\Branch\Api;

interface BranchService
{
    /**
     * Create Branch
     * @param $name
     * @param $description
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $logoFile
     * @param $tags
     * @return mixed
     */
    public function createBranch($name, $description, \Symfony\Component\HttpFoundation\File\UploadedFile $logoFile = null, $tags = null);

    /**
     * Update Branch Info
     * @param $branchId
     * @param $name
     * @param $description
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $logoFile
     * @param $tags
     * @return mixed
     */
    public function updateBranch($branchId, $name, $description, \Symfony\Component\HttpFoundation\File\UploadedFile $logoFile = null, $tags = null);

    /**
     * Delete Branch
     * @param $branchId
     * @return mixed
     */
    public function deleteBranch($branchId);
} 