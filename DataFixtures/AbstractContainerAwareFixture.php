<?php
/**
 * Created by PhpStorm.
 * User: s3nt1nel
 * Date: 8/7/15
 * Time: 3:10 PM
 */

namespace Diamante\DeskBundle\DataFixtures;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractContainerAwareFixture extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @throws \Exception
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        if (is_null($container)) {
            throw new \Exception('Container is not set');
        }

        $this->container = $container;

        $this->init();
    }

    /**
     * @return null
     */
    abstract protected function init();
}