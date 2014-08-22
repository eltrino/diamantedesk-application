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

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository;
use Eltrino\DiamanteDeskBundle\Branch\Model\Factory\BranchFactory;
use Eltrino\DiamanteDeskBundle\Branch\Infrastructure\BranchLogoHandler;
use Eltrino\DiamanteDeskBundle\Branch\Model\Logo;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

class BranchServiceImpl implements BranchService
{
    /**
     * @var BranchRepository
     */
    private $branchRepository;

    /**
     * @var BranchFactory
     */
    private $branchFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Infrastructure\BranchLogoHandler
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
        BranchRepository $branchRepository,
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
     * Create Branch
     * @param $name
     * @param $description
     * @param UploadedFile $logoFile
     * @param $tags
     * @return int|mixed
     */
    public function createBranch($name, $description, \Symfony\Component\HttpFoundation\File\UploadedFile $logoFile = null, $tags = null)
    {
        $this->isGranted('CREATE', 'Entity:EltrinoDiamanteDeskBundle:Branch');

        $logo = null;

        if ($logoFile) {
            $logo = $this->handleLogoUpload($logoFile);
        }

        $branch = $this->branchFactory
            ->create($name, $description, $logo, $tags);

        $this->branchRepository->store($branch);
        $this->tagManager->saveTagging($branch);

        return $branch->getId();
    }

    /**
     * Update Branch Info
     * @param $branchId
     * @param $name
     * @param $description
     * @param UploadedFile $logoFile
     * @param $tags
     * @return int|mixed
     */
    public function updateBranch($branchId, $name, $description, \Symfony\Component\HttpFoundation\File\UploadedFile $logoFile = null, $tags = null)
    {
        $this->isGranted('EDIT', 'Entity:EltrinoDiamanteDeskBundle:Branch');

        $branch = $this->branchRepository->get($branchId);
        /** @var \Symfony\Component\HttpFoundation\File\File $file */
        $file = null;
        if ($logoFile) {
            if ($branch->getLogo()) {
                $this->branchLogoHandler->remove($branch->getLogo());
            }
            $logo = $this->handleLogoUpload($logoFile);
            $file = new Logo($logo->getFilename());
        }

        $branch->update($name, $description, $file);
        $branch->setTags($tags);

        $this->branchRepository->store($branch);
        $this->tagManager->saveTagging($branch);

        return $branch->getId();
    }

    /**
     * Delete Branch
     * @param $branchId
     * @return mixed|void
     */
    public function deleteBranch($branchId)
    {
        $this->isGranted('DELETE', 'Entity:EltrinoDiamanteDeskBundle:Branch');

        $branch = $this->branchRepository->get($branchId);
        if (is_null($branch)) {
            throw new \RuntimeException('Branch loading failed, branch not found. ');
        }
        if ($branch->getLogo()) {
            $this->branchLogoHandler->remove($branch->getLogo());
        }
        $this->branchRepository->remove($branch);
    }

    /**
     * @param BranchFactory $branchFactory
     * @param EntityManager $em
     * @param $branchLogoHandler
     * @param $tagManager
     * @return BranchServiceImpl
     */
    public static function create(BranchFactory $branchFactory,
                                    EntityManager $em,
                                    $branchLogoHandler,
                                    $tagManager,
                                    SecurityFacade $securityFacade
    ) {
        return new BranchServiceImpl(
            $branchFactory,
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Branch'),
            $branchLogoHandler,
            $tagManager,
            $securityFacade
        );
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