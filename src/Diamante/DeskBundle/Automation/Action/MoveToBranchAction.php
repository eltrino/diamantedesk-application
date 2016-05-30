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
use Diamante\DeskBundle\Entity\TicketHistory;
use Doctrine\DBAL\LockMode;
use Proxies\__CG__\Diamante\DeskBundle\Entity\Branch;

/**
 * Class MoveToBranchAction
 *
 * @package Diamante\DeskBundle\Automation\Action
 */
class MoveToBranchAction extends AbstractModifyAction
{
    const ACTION_NAME = 'move_to_branch';
    const TICKET_TYPE = 'ticket';
    const COMMENT_TYPE = 'comment';

    public function execute()
    {
        $branchId = null;
        $target = $this->context->getFact()->getTarget();
        $targetType = $this->context->getFact()->getTargetType();

        if ($this->context->getParameters()->has(static::ACTION_NAME)) {
            $branchId = $this->context->getParameters()->all()[static::ACTION_NAME];
        }

        if (is_null($branchId)) {
            throw new \RuntimeException("Invalid rule configuration");
        }

        $this->em->getConnection()->beginTransaction();

        try {
            /** @var Ticket $ticket */
            $ticket = $this->em->getRepository('DiamanteDeskBundle:Ticket')->get(
                $this->getTicketId($target, $targetType)
            );

            if ($branchId == $ticket->getBranchId()) {
                return;
            }

            /** @var Branch $branch */
            $branch = $this->em->getRepository('DiamanteDeskBundle:Branch')->get($branchId);
            $this->em->lock($branch, LockMode::PESSIMISTIC_READ);
            $this->em->getRepository('DiamanteDeskBundle:TicketHistory')->store(new TicketHistory($ticket));
            $ticket->move($branch);

            $this->disableListeners();
            $this->em->persist($ticket);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
        }
    }

    /**
     * @param array  $target
     * @param string $type
     */
    private function getTicketId(array $target, $type)
    {
        if (static::TICKET_TYPE == $type) {
            return $target['id'];
        } elseif (static::COMMENT_TYPE == $type) {
            return $target['ticket']->getId();
        }

        throw new \RuntimeException('Incorrect target type.');
    }
}