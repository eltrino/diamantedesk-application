<?php

namespace Diamante\DeskBundle\Controller\Shared;

use Symfony\Component\HttpFoundation\RequestStack;

trait RequestGetterTrait
{
    /**
     * @return \Symfony\Component\HttpFoundation\Request|null
     */
    protected function getRequest()
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');

        return $requestStack->getCurrentRequest();
    }
}
