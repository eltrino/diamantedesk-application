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
namespace Diamante\DeskBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\DataAuditBundle\Migration\Extension\AuditFieldExtension;
use Oro\Bundle\DataAuditBundle\Migration\Extension\AuditFieldExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\HttpFoundation\Request;

class DiamanteDeskBundle implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $types = [
            'status', 'priority', 'user_type', 'attachment_file', 'source'
        ];

        $tables = ['oro_audit_field', 'diamante_audit_field'];

        foreach ($tables as $table) {
            foreach ($types as $type) {
                if (!$this->auditFieldTypeExists($schema, $table, $type)) {
                    $this->addAuditFieldType($schema, $type, $type, $table);
                }
            }
        }
    }

    private function auditFieldTypeExists(Schema $schema, $table, $type)
    {
        $table = $schema->getTable($table);

        return ($table->hasColumn(sprintf("old_%s", $type)) && $table->hasColumn(sprintf("new_%", $type)));
    }

    private function addAuditFieldType(Schema $schema, $auditType, $doctrineType, $table)
    {
        $auditFieldTable = $schema->getTable($table);

        $auditFieldTable->addColumn(sprintf('old_%s', $auditType), $doctrineType, [
            'oro_options' => [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM]
            ],
            'notnull' => false
        ]);
        $auditFieldTable->addColumn(sprintf('new_%s', $auditType), $doctrineType, [
            'oro_options' => [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM]
            ],
            'notnull' => false
        ]);
    }
}
