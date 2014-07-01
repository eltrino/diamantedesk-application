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
namespace Eltrino\DiamanteDeskBundle\Migrations\DataFixtures\Demo\ORM\v1_0;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Eltrino\DiamanteDeskBundle\Entity\Ticket;

class LoadTicketData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var UserRepository */
    private $userRepository;

    /** @var BranchRepository */
    private $branchRepository;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
            'Eltrino\DiamanteDeskBundle\Migrations\DataFixtures\Demo\ORM\v1_0\LoadBranchData'
        ];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        /** @var  EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userRepository = $entityManager->getRepository('OroUserBundle:User');
        $this->branchRepository = $entityManager->getRepository('EltrinoDiamanteDeskBundle:Branch');
    }

    public function load(ObjectManager $manager)
    {
        $assignee = $this->userRepository->findOneBy(array('id' => 1));
        $reporter = $this->userRepository->findOneBy(array('id' => 1));

        for ($i = 1; $i <= 10; $i ++) {
            $ticket = new Ticket();
            $ticket->create(
                'ticketSubject' . $i,
                'ticketDescription' . $i,
                $this->branchRepository->findOneBy(array('name' => 'branchName' . $i)),
                'open',
                $reporter,
                $assignee
            );
            $manager->persist($ticket);
        }

        $manager->flush();
    }

}
