<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\MassAction\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassActionHandler;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Datasource\Orm\DeletionIterableResult;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Diamante\DeskBundle\Api\Internal\BranchServiceImpl;

class DeleteBranchMassActionHandler extends DeleteMassActionHandler
{
    /**
     * @var BranchServiceImpl
     */
    protected $branchService;

    /**
     * @param EntityManager $entityManager
     * @param TranslatorInterface $translator
     * @param SecurityFacade $securityFacade
     * @param BranchServiceImpl $branchService
     */
    public function __construct(
        EntityManager $entityManager,
        TranslatorInterface $translator,
        SecurityFacade $securityFacade,
        BranchServiceImpl $branchService
    ) {
        $this->branchService = $branchService;
        parent::__construct($entityManager, $translator, $securityFacade);
    }

    public function handle(MassActionHandlerArgs $args)
    {
        $entityIdentifiedField = $this->getEntityIdentifierField($args);
        $entityName = $this->getEntityName($args);

        /** @var $result ResultRecordInterface */
        $results = new DeletionIterableResult($args->getResults()->getSource());

        foreach ($results as $result) {
            $entity = $this->getEntity($entityName, $result->getValue($entityIdentifiedField));
            if ($this->branchService->isBranchHasTickets($entity->getId())) {
                return new MassActionResponse(
                    false,
                    $this->translator->trans('diamante.desk.branch.messages.delete.exist_tickets_error')
                );
            }

        }

        return parent::handle($args);
    }
}
