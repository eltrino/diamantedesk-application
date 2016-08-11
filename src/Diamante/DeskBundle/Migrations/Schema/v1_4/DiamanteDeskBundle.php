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
        $this->updateDiamanteTicketTable($schema);
        $this->updateDiamanteTicketHistoryTable($schema);
        $this->addDiamanteTicketHistoryForeignKeys($schema);
        $this->updateDiamanteTicketMessageRefTable($schema);
        $this->updateDiamanteAuditFieldTable($schema);
    }

    /**
     * Update diamante_ticket table
     *
     * @param Schema $schema
     */
    protected function updateDiamanteTicketTable(Schema $schema)
    {
        $table = $schema->getTable('diamante_ticket');
        $table->changeColumn('updated_at', ['notnull' => false]);
        $table->addColumn('reporter_email', 'text', []);
    }

    /**
     * Update diamante_ticket_history table
     *
     * @param Schema $schema
     */
    protected function updateDiamanteTicketHistoryTable(Schema $schema)
    {
        $table = $schema->getTable('diamante_ticket_history');
        $table->changeColumn('ticket_id', ['notnull' => false]);
        $table->addIndex(['ticket_id'], 'IDX_CA3F705D700047D2', []);
    }

    /**
     * Update diamante_ticket_message_ref table
     *
     * @param Schema $schema
     */
    protected function updateDiamanteTicketMessageRefTable(Schema $schema)
    {
        $table = $schema->getTable('diamante_ticket_message_ref');
        $table->addColumn('endpoint', 'string', ['notnull' => false, 'length' => 255]);
    }

    /**
     * Add diamante_ticket_history foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteTicketHistoryForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_ticket_history');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_ticket'),
            ['ticket_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Update diamante_audit_field table
     *
     * @param Schema $schema
     */
    protected function updateDiamanteAuditFieldTable(Schema $schema)
    {
        $table = $schema->getTable('diamante_audit_field');
        $table->addColumn('old_status', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.old_status.label', 'description' => 'diamante.desk.auditfield.old_status.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('new_status', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.new_status.label', 'description' => 'diamante.desk.auditfield.new_status.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('old_priority', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.old_priority.label', 'description' => 'diamante.desk.auditfield.old_priority.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('new_priority', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.new_priority.label', 'description' => 'diamante.desk.auditfield.new_priority.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('old_user_type', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.old_user_type.label', 'description' => 'diamante.desk.auditfield.old_user_type.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('new_user_type', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.new_user_type.label', 'description' => 'diamante.desk.auditfield.new_user_type.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('old_attachment_file', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.old_attachment_file.label', 'description' => 'diamante.desk.auditfield.old_attachment_file.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('new_attachment_file', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.new_attachment_file.label', 'description' => 'diamante.desk.auditfield.new_attachment_file.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('old_source', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.old_source.label', 'description' => 'diamante.desk.auditfield.old_source.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addColumn('new_source', 'string', ['notnull' => false, 'length' => 255, 'oro_options' => ['entity' => ['label' => 'diamante.desk.auditfield.new_source.label', 'description' => 'diamante.desk.auditfield.new_source.description'], 'form' => ['is_enabled' => true], 'view' => ['is_displayable' => true], 'extend' => ['owner' => 'System', 'state' => 'Active', 'is_extend' => true, 'is_deleted' => false, 'origin' => 'System']]]);
        $table->addIndex(['old_status'], 'oro_idx_auditfield_old_status', []);
        $table->addIndex(['new_status'], 'oro_idx_auditfield_new_status', []);
        $table->addIndex(['old_priority'], 'oro_idx_aeecf7939be180a', []);
        $table->addIndex(['new_priority'], 'oro_idx_aeecf79325cf500b', []);
        $table->addIndex(['old_user_type'], 'oro_idx_aeecf793b3eb5f51', []);
        $table->addIndex(['new_user_type'], 'oro_idx_aeecf793c4c01e8f', []);
        $table->addIndex(['old_attachment_file'], 'oro_idx_aeecf793ec488a90', []);
        $table->addIndex(['new_attachment_file'], 'oro_idx_aeecf7938762d0dc', []);
        $table->addIndex(['old_source'], 'oro_idx_auditfield_old_source', []);
        $table->addIndex(['new_source'], 'oro_idx_auditfield_new_source', []);
    }
}
