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

use Doctrine\ORM\EntityManager;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Model\Ticket\Priority;

class LoadTicketData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

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

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        /** @var  EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userRepository = $entityManager->getRepository('OroUserBundle:User');
        $this->branchRepository = $entityManager->getRepository('DiamanteDeskBundle:Branch');
    }

    public function load(ObjectManager $manager)
    {
        $assignee = $this->userRepository->findOneBy(array('id' => 1));
        $reporter = $this->userRepository->findOneBy(array('id' => 1));

        for ($i = 1; $i <= 10; $i ++) {
            $ticket = new Ticket(
                'ticketSubject' . $i,
                'ticketDescription' . $i,
                $this->branchRepository->findOneBy(array('name' => 'branchName' . $i)),
                $reporter,
                $assignee,
                Source::PHONE,
                Priority::DEFAULT_PRIORITY,
                Status::OPEN
            );

            $manager->persist($ticket);
        }

        $manager->flush();
    }

}
