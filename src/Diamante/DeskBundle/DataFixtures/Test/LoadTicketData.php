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
namespace Diamante\DeskBundle\DataFixtures\Test;

use Diamante\DeskBundle\DataFixtures\AbstractContainerAwareFixture;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\UserBundle\Model\User;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;

class LoadTicketData extends AbstractContainerAwareFixture implements DependentFixtureInterface
{
    /** @var EntityRepository */
    private $oroUserRepository;

    /** @var EntityRepository */
    private $diamanteUserRepository;

    /** @var EntityRepository */
    private $branchRepository;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Diamante\DeskBundle\DataFixtures\Test\LoadBranchData'
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $oroAssignee = $this->oroUserRepository->findOneBy(array('id' => 1));
        $oroReporter = new User($oroAssignee->getId(), User::TYPE_ORO);

        for ($i = 1; $i <= 10; $i ++) {
            $ticket = $this->createTicket($i, $oroReporter, $oroAssignee);
            $manager->persist($ticket);
        }

        $diamanteAssignee = $this->diamanteUserRepository->findOneBy(array('id' => 1));
        $diamanteReporter = new User($diamanteAssignee->getId(), User::TYPE_DIAMANTE);

        for ($i = 11; $i <= 20; $i ++) {
            $ticket = $this->createTicket($i, $diamanteReporter, $oroAssignee);
            $manager->persist($ticket);
        }

        $manager->flush();
    }

    protected function createTicket($iterator, $reporter, $assignee)
    {
        /** @var \Diamante\DeskBundle\Entity\Branch $branch */
        $branch = $this->branchRepository->findOneBy(array('name' => 'branchName' . $iterator));
        $ticket = new Ticket(
            UniqueId::generate(),
            new TicketSequenceNumber(null),
            'ticketSubject' . $iterator,
            'ticketDescription' . $iterator,
            $branch,
            $reporter,
            $assignee,
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_MEDIUM),
            new Status(Status::OPEN)
        );

        return $ticket;
    }

    /**
     * @return null
     */
    protected function init()
    {
        /** @var  EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine')->getManager();
        $this->oroUserRepository = $entityManager->getRepository('OroUserBundle:User');
        $this->diamanteUserRepository = $entityManager->getRepository('DiamanteUserBundle:DiamanteUser');
        $this->branchRepository = $entityManager->getRepository('DiamanteDeskBundle:Branch');
    }


}
