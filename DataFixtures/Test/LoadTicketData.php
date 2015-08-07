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
    private $userRepository;

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
        $assignee = $this->userRepository->findOneBy(array('id' => 1));
        $reporter = new User($assignee->getId(), User::TYPE_ORO);

        for ($i = 1; $i <= 10; $i ++) {
            $branch = $this->branchRepository->findOneBy(array('name' => 'branchName' . $i));
            $ticket = new Ticket(
                UniqueId::generate(),
                new TicketSequenceNumber(null),
                'ticketSubject' . $i,
                'ticketDescription' . $i,
                $branch,
                $reporter,
                $assignee,
                new Source(Source::PHONE),
                new Priority(Priority::PRIORITY_MEDIUM),
                new Status(Status::OPEN)
            );

            $manager->persist($ticket);
        }

        $manager->flush();
    }

    /**
     * @return null
     */
    protected function init()
    {
        /** @var  EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine')->getManager();
        $this->userRepository = $entityManager->getRepository('OroUserBundle:User');
        $this->branchRepository = $entityManager->getRepository('DiamanteDeskBundle:Branch');
    }


}
