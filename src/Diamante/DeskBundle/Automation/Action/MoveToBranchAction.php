<?php
/*
 * Copyright (c) 2016 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\Automation\Action;

use Diamante\AutomationBundle\Rule\Action\AbstractModifyAction;
use Diamante\DeskBundle\Entity\Ticket;
use Proxies\__CG__\Diamante\DeskBundle\Entity\Branch;

/**
 * Class MoveToBranchAction
 *
 * @package Diamante\DeskBundle\Automation\Action
 */
class MoveToBranchAction extends AbstractModifyAction
{
    public function execute()
    {
        $target = $this->context->getFact()->getTarget();
        /** @var Ticket $ticket */
        $ticket = $this->em->getRepository('DiamanteDeskBundle:Ticket')->get($target['id']);


        $branchId = $this->context->getParameters()->has('branch') ? $this->context->getParameters()->get('branch') : null;

        if (is_null($branchId)) {
            throw new \RuntimeException("Invalid rule configuration");
        }

        /** @var Branch $branch */
        $branch = $this->em->getRepository('DiamanteDeskBundle:Branch')->get($branchId);

        $ticket->move($branch);

        $this->disableListeners();
        $this->em->persist($ticket);
        $this->em->flush();
    }
}