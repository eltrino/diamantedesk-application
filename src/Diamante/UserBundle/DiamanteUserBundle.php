<?php

namespace Diamante\UserBundle;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DiamanteUserBundle extends Bundle
{
    public function boot()
    {
        if (!Type::hasType('user_type')) {
            Type::addType(
                'user_type',
                'Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DBAL\Types\TicketUserType'
            );
        }
    }
}
