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
namespace Diamante\DeskBundle\Infrastructure\Persistence;

use Diamante\DeskBundle\Entity\MessageReference;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Doctrine\ORM\Query;

class DoctrineMessageReferenceRepository extends DoctrineGenericRepository implements MessageReferenceRepository
{
    /**
     * Retrieves Reference by given message id
     * @param string $messageId
     * @return \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference
     */
    public function getReferenceByMessageId($messageId)
    {
        return $this->findOneBy(array('messageId' => $messageId));
    }

    /**
     * Retrieves all Ticket MessageReferences
     * @param Ticket $ticket
     * @return array|MessageReference[]
     */
    public function findAllByTicket(Ticket $ticket)
    {
        return $this->findBy(array('ticket' => $ticket));
    }

    /**
     * Get email which was specified in TO field, when ticket was created via EmailProcessing
     *
     * @param Ticket $ticket
     * @return array|null
     */
    public function getEndpointByTicket(Ticket $ticket)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select("r.endpoint")
            ->from($this->_entityName, 'r')
            ->where($qb->expr()->eq('r.ticket', $ticket->getId()))
            ->setMaxResults(1);

        try {
            $result = $qb->getQuery()->getResult(Query::HYDRATE_SINGLE_SCALAR);
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }
}
