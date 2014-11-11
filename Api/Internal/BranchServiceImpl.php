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

use Diamante\DeskBundle\Api\BranchService;
use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Model\Branch\BranchFactory;
use Diamante\DeskBundle\Infrastructure\Branch\BranchLogoHandler;
use Diamante\DeskBundle\Model\Branch\Logo;
use Diamante\DeskBundle\Model\Shared\Repository;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Diamante\DeskBundle\Model\Branch\Branch;

class BranchServiceImpl implements BranchService
{
    /**
     * @var Repository
     */
    private $branchRepository;

    /**
     * @var BranchFactory
     */
    private $branchFactory;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Branch\BranchLogoHandler
     */
    private $branchLogoHandler;

    /**
     * @var \Oro\Bundle\TagBundle\Entity\TagManager
     */
    private $tagManager;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    public function __construct(
        BranchFactory $branchFactory,
        Repository $branchRepository,
        BranchLogoHandler $branchLogoHandler,
        TagManager $tagManager,
        SecurityFacade $securityFacade
    ) {
        $this->branchFactory     = $branchFactory;
        $this->branchRepository  = $branchRepository;
        $this->branchLogoHandler = $branchLogoHandler;
        $this->tagManager        = $tagManager;
        $this->securityFacade    = $securityFacade;
    }

    /**
     * Retrieves list of all Branches
     * @return Branch[]
     */
    public function listAllBranches()
    {
        $branches = $this->branchRepository->getAll();
        return $branches;
    }

    /**
     * Retrieves Branch by id
     * @param $id
     * @return Branch
     */
    public function getBranch($id)
    {
        $branch = $this->branchRepository->get($id);
        if (is_null($branch)) {
            throw new \RuntimeException('Branch loading failed. Branch not found.');
        }

        $this->tagManager->loadTagging($branch);
        return $branch;
    }

    /**
     * Create Branch
     * @param Command\BranchCommand $branchCommand
     * @return int
     */
    public function createBranch(Command\BranchCommand $branchCommand)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Branch');

        $logo = null;

        if ($branchCommand->logoFile) {
            $logo = $this->handleLogoUpload($branchCommand->logoFile);
        }

        $branch = $this->branchFactory
            ->create(
                $branchCommand->name,
                $branchCommand->description,
                $branchCommand->defaultAssignee,
                $logo,
                $branchCommand->tags
            );

        $this->branchRepository->store($branch);
        $this->tagManager->saveTagging($branch);

        return $branch->getId();
    }

    /**
     * Update Branch
     * @param Command\BranchCommand $branchCommand
     * @return int
     */
    public function updateBranch(Command\BranchCommand $branchCommand)
    {
        $this->isGranted('EDIT', 'Entity:DiamanteDeskBundle:Branch');

        /**
         * @var $branch \Diamante\DeskBundle\Model\Branch\Branch
         */
        $branch = $this->branchRepository->get($branchCommand->id);
        /** @var \Symfony\Component\HttpFoundation\File\File $file */
        $file = null;
        if ($branchCommand->logoFile) {
            if ($branch->getLogo()) {
                $this->branchLogoHandler->remove($branch->getLogo());
            }
            $logo = $this->handleLogoUpload($branchCommand->logoFile);
            $file = new Logo($logo->getFilename());
        }

        $branch->update($branchCommand->name, $branchCommand->description, $branchCommand->defaultAssignee, $file);
        $this->branchRepository->store($branch);

        //TODO: Refactor tag manipulations.
        $this->tagManager->deleteTaggingByParams($branch->getTags(), get_class($branch), $branch->getId());
        $tags = $branchCommand->tags;
        $tags['owner'] = $tags['all'];
        $branch->setTags($tags);
        $this->tagManager->saveTagging($branch);

        return $branch->getId();
    }

    /**
     * Delete Branch
     * @param int $branchId
     * @return void
     */
    public function deleteBranch($branchId)
    {
        $this->isGranted('DELETE', 'Entity:DiamanteDeskBundle:Branch');

        $branch = $this->branchRepository->get($branchId);
        if (is_null($branch)) {
            throw new \RuntimeException('Branch loading failed, branch not found. ');
        }
        if ($branch->getLogo()) {
            $this->branchLogoHandler->remove($branch->getLogo());
        }
        $this->branchRepository->remove($branch);
    }

    public function filterBranches(array $conditions = array())
    {
        if (empty($conditions)) {
            $filteredBranches = $this->branchRepository->getAll();
        } else {
            $filteredBranches = $this->branchRepository->filter($conditions);
        }

        return $filteredBranches;
    }

    /**
     * @param UploadedFile $file
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    private function handleLogoUpload(UploadedFile $file)
    {
        return $this->branchLogoHandler->upload($file);
    }

    /**
     * Verify permissions through Oro Platform security bundle
     *
     * @param $operation
     * @param $entity
     * @throws \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     */
    private function isGranted($operation, $entity)
    {
        if (!$this->securityFacade->isGranted($operation, $entity)) {
            throw new ForbiddenException("Not enough permissions.");
        }
    }
}
