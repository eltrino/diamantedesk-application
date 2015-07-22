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

use Diamante\DeskBundle\Api\ReportTimelineService;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Entity\TicketTimeline;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ReportTimelineServiceImpl
 * @package Diamante\DeskBundle\Api\Internal
 */
class ReportTimelineServiceImpl implements ReportTimelineService
{
    /**
     * @var DoctrineGenericRepository
     */
    protected $timelineRepository;

    /**
     * @var TicketTimeline
     */
    protected $currentDayRecord;

    /**
     * @var array
     */
    protected static $processedNowTickets = [];

    /**
     * @return TicketTimeline|null|object
     */
    protected function getCurrentDayRecord()
    {
        if ($this->currentDayRecord) {
            return $this->currentDayRecord;
        } else {
            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $date->setTime(0, 0, 0);
            $this->currentDayRecord = $this->timelineRepository->findOneBy(['date' => $date]);
        }

        if (!$this->currentDayRecord) {
            $this->currentDayRecord = new TicketTimeline($date);
        }

        return $this->currentDayRecord;
    }

    protected function storeCurrentDayRecord()
    {
        $this->timelineRepository->store($this->currentDayRecord);
    }

    /**
     * @param OnFlushEventArgs $event
     * @param ContainerInterface $container
     * @return mixed
     */
    public function onFlush(OnFlushEventArgs $event, ContainerInterface $container)
    {
        $this->timelineRepository = $container->get('diamante.ticket_timeline.repository');

        $em = $event->getEntityManager();
        $uof = $em->getUnitOfWork();

        foreach ($uof->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Ticket) {
                if (in_array($entity->getId(), static::$processedNowTickets)) {
                    return;
                }
                if ((string)$entity->getStatus()->getValue() === 'new') {
                    $this->increaseNewCounter();
                    static::$processedNowTickets[] = $entity->getId();
                    $this->storeCurrentDayRecord();
                }
            }
        }

        foreach ($uof->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Ticket) {
                if (in_array($entity->getId(), static::$processedNowTickets)) {
                    return;
                }
                $changes = $uof->getEntityChangeSet($entity);
                if (isset($changes['status'])) {

                    $from = $changes['status'][0]->getValue();
                    $to = $changes['status'][1]->getValue();

                    if ($to === 'resolved') {
                        $this->increaseSolvedCounter();
                    }

                    if ($to === 'closed') {
                        $this->increaseClosedCounter();
                    }

                    if (($from === 'closed' || $from === 'resolved') &&
                        ($to !== 'closed' && $to !== 'resolved')
                    ) {
                        $this->increaseReopenCounter();
                    }

                    static::$processedNowTickets[] = $entity->getId();
                    $this->storeCurrentDayRecord();
                }
            }
        }
    }

    private function increaseNewCounter()
    {
        $this->getCurrentDayRecord()->setNew(
            $this->getCurrentDayRecord()->getNew() + 1
        );
    }

    private function increaseSolvedCounter()
    {
        $this->getCurrentDayRecord()->setSolved(
            $this->getCurrentDayRecord()->getSolved() + 1
        );
    }

    private function increaseClosedCounter()
    {
        $this->getCurrentDayRecord()->setClosed(
            $this->getCurrentDayRecord()->getClosed() + 1
        );
    }

    private function increaseReopenCounter()
    {
        $this->getCurrentDayRecord()->setReopen(
            $this->getCurrentDayRecord()->getReopen() + 1
        );
    }

}