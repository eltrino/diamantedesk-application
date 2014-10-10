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
namespace Diamante\DeskBundle\Api\Internal;

use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Infrastructure\Ticket\Filters\FilterUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketGridFiltersService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Repository
     */
    private $filterRepository;

    /**
     * @param ContainerInterface $container
     * @param Repository $filterRepository
     */
    public function __construct(ContainerInterface $container, Repository $filterRepository)
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
        /**
         * @var $filter \Diamante\DeskBundle\Model\Ticket\Filter
         */
        $filter = $this->filterRepository->get($filterId);

        if (!$filter) {
            throw new \RuntimeException('Filter loading failed, filter not found.');
        }

        $concreteFilterUrlGenerator = $this->container->get($filter->getServiceId());

        if (!$concreteFilterUrlGenerator) {
            throw new \RuntimeException('Filter generator loading failed, filter generator not found.');
        }

        if (!($concreteFilterUrlGenerator instanceof FilterUrlGeneratorInterface)) {
            throw new \InvalidArgumentException(sprintf('Object should be an instance of FilterUrlGeneratorInterface.'));
        }

        return $concreteFilterUrlGenerator->generateFilterUrlPart();
    }

    public static function create(ContainerInterface $container, Repository $filterRepository)
    {
        return new TicketGridFiltersService(
            $container,
            $filterRepository
        );
    }
}
