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
namespace Diamante\DeskBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\HttpFoundation\Request;

class DiamanteDeskBundle implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('diamante_branch');

        $table->addColumn(
            'number',
            'integer',
            [
                'notnull' => true,
                'default' => 1
            ]
        );
    }
}
