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
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Eltrino\DiamanteDeskBundle\Entity\Attachment;

class LoadAttachmentData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var TicketRepository */
    private $ticketRepository;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Eltrino\DiamanteDeskBundle\Migrations\DataFixtures\Demo\ORM\v1_0\LoadTicketData'
        ];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        /** @var  EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->ticketRepository = $entityManager->getRepository('EltrinoDiamanteDeskBundle:Ticket');
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i ++) {
            $attachment = new Attachment('fileName' . $i);

            $manager->persist($attachment);
        }

        $manager->flush();
    }

}
