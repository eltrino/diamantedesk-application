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

namespace Diamante\AutomationBundle\Rule\Repository;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\AutomationBundle\Infrastructure\Persistence\DoctrineBusinessRulesRepository;

class RepositoryProviderImpl implements RepositoryProvider
{
    /**
     * @var array
     */
    protected $repositories = [];

    /**
     * @var array
     */
    protected $targetList = [];

    /**
     * @param DoctrineBusinessRulesRepository $businessRulesRepository
     */
    public function __construct(DoctrineBusinessRulesRepository $businessRulesRepository)
    {
        $this->targetList = $businessRulesRepository->getTargetList();
    }

    /**
     * @param DoctrineGenericRepository $repository
     */
    public function addRepository(DoctrineGenericRepository $repository)
    {
        if(in_array($this->getEntityName($repository), $this->targetList)) {
            $this->repositories[] = $repository;
        }
    }

    /**
     * @return array
     */
    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * @param DoctrineGenericRepository $repository
     * @return string
     */
    private function getEntityName($repository) {
        $className = $repository->getClassName();
        $entityName = array_pop(explode('\\', $className));

        return strtolower($entityName);
    }
}