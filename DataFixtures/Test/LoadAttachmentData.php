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
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Diamante\DeskBundle\Entity\Attachment;
use Diamante\DeskBundle\Model\Attachment\File;

class LoadAttachmentData extends AbstractContainerAwareFixture implements  DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Diamante\DeskBundle\DataFixtures\Test\LoadTicketData'
        ];
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i ++) {
            $name = 'fileName' . $i;
            $attachment = new Attachment(new File($name), md5($name));

            $manager->persist($attachment);
        }

        $image = $this->container->get('kernel')
            ->locateResource('@DiamanteDeskBundle/Tests/Functional/fixture/test.jpg');
        $attachment = new Attachment(new File($image), md5($image));
        $manager->persist($attachment);

        $manager->flush();
    }

    /**
     * @return null
     */
    protected function init()
    {}
}
