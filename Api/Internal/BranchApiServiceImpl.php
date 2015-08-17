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
namespace Diamante\DeskBundle\Api\Internal;

use Diamante\ApiBundle\Annotation\ApiDoc;
use Diamante\ApiBundle\Routing\RestServiceInterface;
use Diamante\DeskBundle\Api\ApiPagingService;
use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Model\Branch\DuplicateBranchKeyException;
use Diamante\DeskBundle\Model\Branch\Filter\BranchFilterCriteriaProcessor;

class BranchApiServiceImpl extends BranchServiceImpl implements RestServiceInterface
{
    use ApiServiceImplTrait;
    /**
     * @var ApiPagingService
     */
    private $apiPagingService;

    /**
     * Retrieves list of all Branches. Filters branches with parameters provided within GET request
     * Time filtering parameters as well as paging/sorting configuration parameters can be found in \Diamante\DeskBundle\Api\Command\CommonFilterCommand class.
     * Time filtering values should be converted to UTC
     *
     * @ApiDoc(
     *  description="Returns all branches",
     *  uri="/branches.{_format}",
     *  method="GET",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to list branches"
     *  }
     * )
     * @param Command\Filter\FilterBranchesCommand $command
     * @return \Diamante\DeskBundle\Model\Branch\Branch[]
     */
    public function listAllBranches(Command\Filter\FilterBranchesCommand $command)
    {
        $processor = new BranchFilterCriteriaProcessor();
        $repository = $this->getBranchRepository();
        $pagingProperties = $this->buildPagination($processor, $repository, $command, $this->apiPagingService);
        $criteria = $processor->getCriteria();

        $branches = $repository->filter($criteria, $pagingProperties);

        return $branches;
    }

    /**
     * Retrieves Branch by id
     *
     * @ApiDoc(
     *  description="Returns a branch",
     *  uri="/branches/{id}.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Branch Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to see branch",
     *      404="Returned when the branch is not found"
     *  }
     * )
     *
     * @param $id
     * @return \Diamante\DeskBundle\Model\Branch\Branch
     */
    public function getBranch($id)
    {
        return parent::getBranch($id);
    }

    /**
     * Create Branch
     *
     * @ApiDoc(
     *  description="Create branch",
     *  uri="/branches.{_format}",
     *  method="POST",
     *  resource=true,
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized to create branch"
     *  }
     * )
     *
     * @param Command\BranchCommand $branchCommand
     * @return \Diamante\DeskBundle\Model\Branch\Branch
     * @throws DuplicateBranchKeyException
     */
    public function createBranch(Command\BranchCommand $branchCommand)
    {
        return parent::createBranch($branchCommand);
    }

    /**
     * Update certain properties of the Branch
     *
     * @ApiDoc(
     *  description="Update branch",
     *  uri="/branches/{id}.{_format}",
     *  method={
     *      "PUT",
     *      "PATCH"
     *  },
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Branch Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to update branch",
     *      404="Returned when the branch is not found"
     *  }
     * )
     *
     * @param Command\UpdatePropertiesCommand $command
     * @return \Diamante\DeskBundle\Model\Branch\Branch
     */
    public function updateProperties(Command\UpdatePropertiesCommand $command)
    {
        return parent::updateProperties($command);
    }

    /**
     * Delete Branch
     *
     * @ApiDoc(
     *  description="Delete branch",
     *  uri="/branches/{id}.{_format}",
     *  method="DELETE",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Branch Id"
     *      }
     *  },
     *  statusCodes={
     *      204="Returned when successful",
     *      403="Returned when the user is not authorized to delete branch",
     *      404="Returned when the branch is not found"
     *  }
     * )
     *
     * @param int $id
     * @return void
     */
    public function deleteBranch($id)
    {
        parent::deleteBranch($id);
    }

    /**
     * @param ApiPagingService $pagingService
     */
    public function setApiPagingService(ApiPagingService $pagingService)
    {
        $this->apiPagingService = $pagingService;
    }
}
