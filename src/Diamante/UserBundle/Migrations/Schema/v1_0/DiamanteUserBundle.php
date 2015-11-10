<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\UserBundle\Migrations\Schema\v1_0;


use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DiamanteUserBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createDiamanteApiUserTable($schema);
        $this->createDiamanteUserTable($schema);

        /** Foreign keys generation **/
        $this->addDiamanteApiUserForeignKeys($schema);
        $this->addDiamanteUserForeignKeys($schema);
    }

    /**
     * Create diamante_api_user table
     *
     * @param Schema $schema
     */
    protected function createDiamanteApiUserTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_api_user');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('diamante_user', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('salt', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('is_active', 'boolean', []);
        $table->addColumn('hash', 'string', ['length' => 255, 'comment' => 'Hash used for confirmation, password reset.']);
        $table->addColumn('hash_expiration_time', 'integer', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'UNIQ_E244C0A0E7927C74');
        $table->addUniqueIndex(['diamante_user'], 'UNIQ_E244C0A019757A1B');
    }

    /**
     * Create diamante_user table
     *
     * @param Schema $schema
     */
    protected function createDiamanteUserTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_user');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('api_user', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('is_deleted', 'boolean', ['default' => '0']);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'UNIQ_19757A1BE7927C74');
        $table->addUniqueIndex(['api_user'], 'UNIQ_19757A1BAC64A0BA');
    }

    /**
     * Add diamante_api_user foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteApiUserForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_api_user');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_user'),
            ['diamante_user'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_user foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteUserForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_user');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_api_user'),
            ['api_user'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}