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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Entity\Comment;
use Diamante\DeskBundle\Model\User\User;

class LoadCommentData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var EntityRepository */
    private $userRepository;

    /** @var EntityRepository */
    private $ticketRepository;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Diamante\DeskBundle\DataFixtures\Test\LoadTicketData'
        ];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        /** @var  EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userRepository = $entityManager->getRepository('OroUserBundle:User');
        $this->ticketRepository = $entityManager->getRepository('DiamanteDeskBundle:Ticket');
    }

    public function load(ObjectManager $manager)
    {
        $author = $this->userRepository->findOneBy(array('id' => 1));
        for ($i = 1; $i <= 10; $i ++) {
            $ticket = $this->ticketRepository->findOneBy(array('subject' => 'ticketSubject' . $i));
            for($j = 1; $j <= 10; $j++) {
                $comment = new Comment(
                    'commentContent' . $i . '-' . $j,
                    $ticket,
                    new User($author->getId(), User::TYPE_ORO)
                );
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

}
