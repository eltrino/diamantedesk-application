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
namespace Diamante\DeskBundle\Model\Ticket;

use Oro\Bundle\UserBundle\Entity\User;

interface TicketBuilder
{
    /**
     * @param TicketSequenceNumber $sequenceNumber
     * @return $this
     */
    public function setSequenceNumber(TicketSequenceNumber $sequenceNumber);

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject);

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @param int $id
     * @return $this
     */
    public function setBranchId($id);

    /**
     * @param string $id
     * @return $this
     */
    public function setReporter($id);

    /**
     * @param int|User $identity
     * @return $this
     */
    public function setAssignee($identity);

    /**
     * @param string $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * @param string $source
     * @return $this
     */
    public function setSource($source);

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Builds Ticket object and unset all previously defined values
     * @return Ticket
     */
    public function build();
}
