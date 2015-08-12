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
use Diamante\DeskBundle\Model\Branch\Exception\BranchCreateException;
use Diamante\DeskBundle\Model\Branch\Exception\BranchDeleteException;
use Diamante\DeskBundle\Model\Branch\Exception\BranchNotFoundException;
use Diamante\DeskBundle\Model\Branch\Exception\BranchSaveException;
use Diamante\DeskBundle\Model\Branch\Exception\DuplicateBranchKeyException;
use Diamante\DeskBundle\Model\Branch\Logo;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
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
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $doctrineRegistry,
        BranchFactory $branchFactory,
        Repository $branchRepository,
        BranchLogoHandler $branchLogoHandler,
        TagManager $tagManager,
        AuthorizationService $authorizationService,
        UserService $userService
    ) {
        $this->branchFactory     = $branchFactory;
        $this->branchRepository  = $branchRepository;
        $this->branchLogoHandler = $branchLogoHandler;
        $this->tagManager        = $tagManager;
        $this->authorizationService = $authorizationService;
        $this->userService       = $userService;
        $this->registry          = $doctrineRegistry;
    }

    /**
     * Retrieves list of all Branches.
     *
     * @return Branch[]
     */
    public function getAllBranches()
    {
        $this->isGranted('VIEW', 'Entity:DiamanteDeskBundle:Branch');

        return $this->branchRepository->getAll();
    }

    /**
     * Retrieves Branch by id
     * @param $id
     * @return Branch
     */
    public function getBranch($id)
    {
        $this->isGranted('VIEW', 'Entity:DiamanteDeskBundle:Branch');
        $branch = $this->branchRepository->get($id);

        if (is_null($branch)) {
            throw new BranchNotFoundException();
        }

        $this->tagManager->loadTagging($branch);
        return $branch;
    }

    /**
     * Create Branch
     * @param Command\BranchCommand $branchCommand
     * @return \Diamante\DeskBundle\Entity\Branch
     * @throws DuplicateBranchKeyException
     */
    public function createBranch(Command\BranchCommand $branchCommand)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Branch');

        $logo = $this->uploadBranchLogoIfExists($branchCommand);
        $originalLogoFileName = $logo->getOriginalName();
        $assignee = $this->extractDefaultBranchAssignee($branchCommand);

        try {
            $branch = $this->branchFactory
                ->create(
                    $branchCommand->name,
                    $branchCommand->description,
                    $branchCommand->key,
                    $assignee,
                    $logo,
                    $originalLogoFileName,
                    $branchCommand->tags
                );

            $this->registry->getManager()->persist($branch);
            $this->tagManager->saveTagging($branch);

            $this->registry->getManager()->flush();

            return $branch;
        } catch (\Exception $e) {
            throw new BranchCreateException($e->getMessage());
        }
    }

    /**
     * Update Branch
     *
     * @param Command\BranchCommand $branchCommand
     * @return int
     */
    public function updateBranch(Command\BranchCommand $branchCommand)
    {
        $this->isGranted('EDIT', 'Entity:DiamanteDeskBundle:Branch');

        /**
         * @var $branch \Diamante\DeskBundle\Entity\Branch
         */
        $branch = $this->branchRepository->get($branchCommand->id);

        $assignee = $this->extractDefaultBranchAssignee($branchCommand);
        if($branchCommand->isRemoveLogo() || $branchCommand->logoFile) {
            $this->removeBranchLogo($branch);
        } 

        $file = $this->uploadBranchLogoIfExists($branchCommand);

        try {
            $branch->update($branchCommand->name, $branchCommand->description, $assignee, $file);
            $this->registry->getManager()->persist($branch);
            $this->handleTagging($branchCommand, $branch);

            $this->registry->getManager()->flush();
            $this->tagManager->saveTagging($branch);

            return $branch->getId();
        } catch (\Exception $e) {
            throw new BranchSaveException($e->getMessage());
        }
    }

    /**
     * Update certain properties of the Branch
     * @param Command\UpdatePropertiesCommand $command
     * @return Branch
     */
    public function updateProperties(Command\UpdatePropertiesCommand $command)
    {
        $this->isGranted('EDIT', 'Entity:DiamanteDeskBundle:Branch');

        /**
         * @var $branch \Diamante\DeskBundle\Entity\Branch
         */
        $branch = $this->branchRepository->get($command->id);
        if (is_null($branch)) {
            throw new BranchNotFoundException();
        }

        try {
            foreach ($command->properties as $name => $value) {
                $branch->updateProperty($name, $value);
            }

            $this->branchRepository->store($branch);

            return $branch;
        } catch (\Exception $e) {
            throw new BranchSaveException($e->getMessage());
        }
    }

    /**
     * Delete Branch
     * @param int $branchId
     * @return void
     */
    public function deleteBranch($branchId)
    {
        $this->isGranted('DELETE', 'Entity:DiamanteDeskBundle:Branch');

        /** @var Branch $branch */
        $branch = $this->branchRepository->get($branchId);
        if (is_null($branch)) {
            throw new BranchNotFoundException();
        }

        try {
            if ($branch->getLogo()) {
                $this->branchLogoHandler->remove($branch->getLogo());
            }
            $this->branchRepository->remove($branch);
        } catch (\Exception $e) {
            throw new BranchDeleteException($e->getMessage());
        }
    }

    /**
     * @param Command\BranchCommand $command
     * @return Logo|null
     * @throws \Diamante\DeskBundle\Model\Branch\Exception\LogoHandlerLogicException
     */
    private function uploadBranchLogoIfExists(Command\BranchCommand $command)
    {
        /** @var File $command->logoFile */
        if (!empty($command->logoFile)) {
            $logo = $this->branchLogoHandler->upload($command->logoFile);
            return new Logo($logo, $command->logoFile->getOriginalClientName());
        }

        return null;
    }

    /**
     * Verify permissions through Oro Platform security bundle
     *
     * @param string $operation
     * @param $entity
     * @throws \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     */
    private function isGranted($operation, $entity)
    {
        if (!$this->authorizationService->isActionPermitted($operation, $entity)) {
            throw new ForbiddenException("Not enough permissions.");
        }
    }

    /**
     * @return Repository
     */
    protected function getBranchRepository()
    {
        return $this->branchRepository;
    }

    /**
     * @param Command\BranchCommand $command
     * @return \Diamante\UserBundle\Entity\DiamanteUser|null|\Oro\Bundle\UserBundle\Entity\User
     */
    protected function extractDefaultBranchAssignee(Command\BranchCommand $command)
    {
        $assignee = null;

        if ($command->defaultAssignee !== null) {
            $assignee = $this->userService->getByUser(new User($command->defaultAssignee, User::TYPE_ORO));
        }

        return $assignee;
    }

    /**
     * @param Branch $branch
     * @return Logo
     */
    protected function removeBranchLogo(Branch $branch)
    {
        if (null !== $branch->getLogo()) {
            $this->branchLogoHandler->remove($branch->getLogo());
        }

        return new Logo();
    }

    /**
     * @param Command\BranchCommand $command
     * @param Branch $branch
     */
    protected function handleTagging(Command\BranchCommand $command, Branch $branch)
    {
        $tags = $command->getTags();
        $tags['owner'] = $tags['all'];
        $branch->setTags($tags);
    }
}
