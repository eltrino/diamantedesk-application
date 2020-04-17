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
use Diamante\DeskBundle\Model\Branch\Exception\BranchNotFoundException;
use Diamante\DeskBundle\Model\Branch\Logo;
use Diamante\DeskBundle\Model\Shared\Repository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Diamante\DeskBundle\Model\Branch\Branch;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @var BranchLogoHandler
     */
    private $branchLogoHandler;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $doctrineRegistry,
        BranchFactory $branchFactory,
        Repository $branchRepository,
        BranchLogoHandler $branchLogoHandler,
        AuthorizationService $authorizationService
    ) {
        $this->branchFactory        = $branchFactory;
        $this->branchRepository     = $branchRepository;
        $this->branchLogoHandler    = $branchLogoHandler;
        $this->authorizationService = $authorizationService;
        $this->registry             = $doctrineRegistry;
    }

    /**
     * Retrieves list of all Branches.
     *
     * @return Branch[]
     */
    public function getAllBranches()
    {
        $this->isGranted('VIEW', 'Entity:DiamanteDeskBundle:Branch');
        /** @var Branch[] $branches */
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
        $this->isGranted('VIEW', 'Entity:DiamanteDeskBundle:Branch');
        /** @var Branch $branch */
        $branch = $this->branchRepository->get($id);

        if ($branch === null) {
            throw new BranchNotFoundException();
        }

        return $branch;
    }

    /**
     * Create Branch
     * @param Command\BranchCommand $branchCommand
     * @return \Diamante\DeskBundle\Entity\Branch
     */
    public function createBranch(Command\BranchCommand $branchCommand)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Branch');

        $logo = $this->uploadBranchLogoIfExists($branchCommand);
        $assignee = $this->extractDefaultBranchAssignee($branchCommand);

        $branch = $this->branchFactory
            ->create(
                $branchCommand->name,
                $branchCommand->description,
                $branchCommand->key,
                $assignee,
                $logo
            );

        $this->registry->getManager()->persist($branch);
        $this->registry->getManager()->flush();

        return $branch;
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
        $file = $this->uploadBranchLogoIfExists($branchCommand);
        if ($branchCommand->isRemoveLogo()) {
            $this->removeBranchLogo($branch);
            if (!$branchCommand->logoFile) {
                $file = new Logo();
            }
        } 
        
        $branch->update($branchCommand->name, $branchCommand->description, $assignee, $file);
        $this->registry->getManager()->persist($branch);

        $this->registry->getManager()->flush();

        return $branch->getId();
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

        foreach ($command->properties as $name => $value) {
            $branch->updateProperty($name, $value);
        }

        $this->branchRepository->store($branch);

        return $branch;
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

        if ($branch->getLogo()) {
            $this->branchLogoHandler->remove($branch->getLogo());
        }
        $this->branchRepository->remove($branch);
    }

    /**
     * @param Command\BranchCommand $command
     * @return Logo|null
     * @throws \Diamante\DeskBundle\Model\Branch\Exception\LogoHandlerLogicException
     */
    private function uploadBranchLogoIfExists(Command\BranchCommand $command)
    {
        /** @var UploadedFile $command->logoFile */
        if (!empty($command->logoFile)) {
            $logo = $this->branchLogoHandler->upload($command->logoFile);
            return new Logo($logo->getFilename(), $command->logoFile->getClientOriginalName());
        }

        return null;
    }

    /**
     * Verify permissions through Oro Platform security bundle
     *
     * @param string $operation
     * @param string $entity
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
            $assignee = $this->registry->getRepository('OroUserBundle:User')->find($command->defaultAssignee);
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
     * @param integer $id
     * @return bool
     */
    public function isBranchHasTickets($id)
    {
        if ($this->registry->getRepository('DiamanteDeskBundle:Ticket')->findOneBy(array('branch' => $id))) {
            return true;
        }
        return false;
    }
}

