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
namespace Eltrino\DiamanteDeskBundle\Ticket\Api\Internal;

use Doctrine\ORM\EntityManager;
use Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Filters\FilterUrlGeneratorInterface;
use Eltrino\DiamanteDeskBundle\Ticket\Model\FilterRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketGridFiltersService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FilterRepository
     */
    private $filterRepository;

    /**
     * @param ContainerInterface $container
     * @param FilterRepository $filterRepository
     */
    function __construct(ContainerInterface $container, FilterRepository $filterRepository)
    {
        $this->container = $container;
        $this->filterRepository = $filterRepository;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filterRepository->getAll();
    }

    /**
     * @param string $filterId
     * @return mixed
     */
    public function generateGridFilterUrl($filterId)
    {
        $filter = $this->filterRepository->get($filterId);

        if (!$filter) {
            throw new \RuntimeException('Filter not found.');
        }

        $concreteFilterUrlGenerator = $this->container->get($filter->getServiceId());

        if (!$concreteFilterUrlGenerator) {
            throw new \RuntimeException('Concrete Filter Generator not found.');
        }

        if (!($concreteFilterUrlGenerator instanceof FilterUrlGeneratorInterface)) {
            throw new \InvalidArgumentException($concreteFilterUrlGenerator . " should be an instance of FilterUrlGeneratorInterface.");
        }

        return $concreteFilterUrlGenerator->generateFilterUrlPart();
    }

    public static function create(ContainerInterface $container, EntityManager $em)
    {
        return new TicketGridFiltersService(
            $container,
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Filter')
        );
    }
} 