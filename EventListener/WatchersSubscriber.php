<?php

namespace Diamante\DeskBundle\EventListener;

use Diamante\DeskBundle\Model\Ticket\Notifications\Events\AbstractTicketEvent;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class WatchersSubscriber implements EventSubscriberInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            'ticketWasCreated'         => 'processEvent',
            'ticketWasUpdated'         => 'processEvent',
            'ticketAssigneeWasChanged' => 'processEvent',
        ];
    }

    /**
     * @param AbstractTicketEvent $event
     */
    public function processEvent($event)
    {
        $watchersService = $this->container->get('diamante.ticket.watcher_list.service');

        $ticket = $this->getTicket($event);

        foreach ($this->getMemberList($ticket, $event) as $user) {
            if ($user instanceof User) {
                $watchersService->addWatcher($ticket, $user);
            }
        }
    }

    /**
     * @param AbstractTicketEvent $event
     * @return Ticket
     */
    private function getTicket(AbstractTicketEvent $event)
    {
        $ticketRepository = $this->container->get('diamante.ticket.repository');
        $uniqueId = new UniqueId($event->getAggregateId());

        return $ticketRepository->getByUniqueId($uniqueId);
    }

    /**
     * @param Ticket $ticket
     * @param AbstractTicketEvent $event
     * @return array
     */
    private function getMemberList(Ticket $ticket, AbstractTicketEvent $event)
    {
        $members = [];

        if ($ticket->getAssignee()) {
            $members[] = new User($ticket->getAssignee()->getId(), User::TYPE_ORO);
        }

        if ($event->getEventName() == 'ticketAssigneeWasChanged') {
            return $members;
        }

        $members[] = $ticket->getReporter();

        return $members;
    }

}