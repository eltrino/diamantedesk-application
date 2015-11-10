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

namespace Diamante\DeskBundle\Migrations\Schema\v1_0;


use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DiamanteDeskBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createDiamanteAttachmentTable($schema);
        $this->createDiamanteAuditTable($schema);
        $this->createDiamanteAuditFieldTable($schema);
        $this->createDiamanteBranchTable($schema);
        $this->createDiamanteBranchEmailConfigurationTable($schema);
        $this->createDiamanteCommentTable($schema);
        $this->createDiamanteCommentAttachmentsTable($schema);
        $this->createDiamanteTicketTable($schema);
        $this->createDiamanteTicketAttachmentsTable($schema);
        $this->createDiamanteTicketHistoryTable($schema);
        $this->createDiamanteTicketMessageReferenceTable($schema);
        $this->createDiamanteTicketTimelineTable($schema);
        $this->createDiamanteWatcherListTable($schema);

        /** Foreign keys generation **/
        $this->addDiamanteAuditForeignKeys($schema);
        $this->addDiamanteAuditFieldForeignKeys($schema);
        $this->addDiamanteBranchForeignKeys($schema);
        $this->addDiamanteBranchEmailConfigurationForeignKeys($schema);
        $this->addDiamanteCommentForeignKeys($schema);
        $this->addDiamanteCommentAttachmentsForeignKeys($schema);
        $this->addDiamanteTicketForeignKeys($schema);
        $this->addDiamanteTicketAttachmentsForeignKeys($schema);
        $this->addDiamanteTicketMessageReferenceForeignKeys($schema);
        $this->addDiamanteWatcherListForeignKeys($schema);
    }

    /**
     * Create diamante_attachment table
     *
     * @param Schema $schema
     */
    protected function createDiamanteAttachmentTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_attachment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('file', 'string', ['length' => 255]);
        $table->addColumn('hash', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create diamante_audit table
     *
     * @param Schema $schema
     */
    protected function createDiamanteAuditTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_audit');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('action', 'string', ['length' => 8]);
        $table->addColumn('logged_at', 'datetime', []);
        $table->addColumn('object_id', 'integer', ['notnull' => false]);
        $table->addColumn('object_class', 'string', ['length' => 255]);
        $table->addColumn('object_name', 'string', ['length' => 255]);
        $table->addColumn('version', 'integer', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id'], 'IDX_17E9290DA76ED395', []);
        $table->addIndex(['organization_id'], 'IDX_17E9290D32C8A3DE', []);
        $table->addIndex(['logged_at'], 'idx_diamante_audit_logged_at', []);
    }

    /**
     * Create diamante_audit_field table
     *
     * @param Schema $schema
     */
    protected function createDiamanteAuditFieldTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_audit_field');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('audit_id', 'integer', []);
        $table->addColumn('field', 'string', ['length' => 255]);
        $table->addColumn('visible', 'boolean', ['default' => '1']);
        $table->addColumn('data_type', 'string', ['length' => 255]);
        $table->addColumn('old_integer', 'bigint', ['notnull' => false]);
        $table->addColumn('old_float', 'float', ['notnull' => false]);
        $table->addColumn('old_boolean', 'boolean', ['notnull' => false]);
        $table->addColumn('old_text', 'text', ['notnull' => false]);
        $table->addColumn('old_date', 'date', ['notnull' => false]);
        $table->addColumn('old_time', 'time', ['notnull' => false]);
        $table->addColumn('old_datetime', 'datetime', ['notnull' => false]);
        $table->addColumn('old_datetimetz', 'datetime', ['notnull' => false]);
        $table->addColumn('old_object', 'object', ['notnull' => false, 'comment' => '(DC2Type:object)']);
        $table->addColumn('old_array', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('old_simplearray', 'simple_array', ['notnull' => false, 'comment' => '(DC2Type:simple_array)']);
        $table->addColumn('old_jsonarray', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('new_integer', 'bigint', ['notnull' => false]);
        $table->addColumn('new_float', 'float', ['notnull' => false]);
        $table->addColumn('new_boolean', 'boolean', ['notnull' => false]);
        $table->addColumn('new_text', 'text', ['notnull' => false]);
        $table->addColumn('new_date', 'date', ['notnull' => false]);
        $table->addColumn('new_time', 'time', ['notnull' => false]);
        $table->addColumn('new_datetime', 'datetime', ['notnull' => false]);
        $table->addColumn('new_datetimetz', 'datetime', ['notnull' => false]);
        $table->addColumn('new_object', 'object', ['notnull' => false, 'comment' => '(DC2Type:object)']);
        $table->addColumn('new_array', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('new_simplearray', 'simple_array', ['notnull' => false, 'comment' => '(DC2Type:simple_array)']);
        $table->addColumn('new_jsonarray', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['audit_id'], 'IDX_139C4C28BD29F359', []);
    }

    /**
     * Create diamante_branch table
     *
     * @param Schema $schema
     */
    protected function createDiamanteBranchTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_branch');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('default_assignee_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false, 'length' => 65535]);
        $table->addColumn('branch_key', 'string', ['length' => 255]);
        $table->addColumn('logo', 'string', ['length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['branch_key'], 'UNIQ_EC6B5FECEE98ECC2');
        $table->addIndex(['default_assignee_id'], 'IDX_EC6B5FEC8D1F6ED6', []);
    }

    /**
     * Create diamante_branch_email_config table
     *
     * @param Schema $schema
     */
    protected function createDiamanteBranchEmailConfigurationTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_branch_email_config');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('branch_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_domains', 'text', ['notnull' => false]);
        $table->addColumn('support_address', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['branch_id'], 'UNIQ_E7D21C30DCD6CC49');
    }

    /**
     * Create diamante_comment table
     *
     * @param Schema $schema
     */
    protected function createDiamanteCommentTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_comment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('ticket_id', 'integer', ['notnull' => false]);
        $table->addColumn('content', 'text', []);
        $table->addColumn('author_id', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('private', 'boolean', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['ticket_id'], 'IDX_B0971C8E700047D2', []);
    }

    /**
     * Create diamante_comment_attachments table
     *
     * @param Schema $schema
     */
    protected function createDiamanteCommentAttachmentsTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_comment_attachments');
        $table->addColumn('comment_id', 'integer', []);
        $table->addColumn('attachment_id', 'integer', []);
        $table->setPrimaryKey(['comment_id', 'attachment_id']);
        $table->addIndex(['comment_id'], 'IDX_5F70F1D7F8697D13', []);
        $table->addIndex(['attachment_id'], 'IDX_5F70F1D7464E68B', []);
    }

    /**
     * Create diamante_ticket table
     *
     * @param Schema $schema
     */
    protected function createDiamanteTicketTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_ticket');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('assignee_id', 'integer', ['notnull' => false]);
        $table->addColumn('branch_id', 'integer', ['notnull' => false]);
        $table->addColumn('unique_id', 'string', ['length' => 255]);
        $table->addColumn('number', 'integer', []);
        $table->addColumn('subject', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', []);
        $table->addColumn('status', 'string', ['length' => 255]);
        $table->addColumn('priority', 'string', ['length' => 255]);
        $table->addColumn('reporter_id', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('source', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['branch_id'], 'IDX_C04DE950DCD6CC49', []);
        $table->addIndex(['assignee_id'], 'IDX_C04DE95059EC7D60', []);
    }

    /**
     * Create diamante_ticket_attachments table
     *
     * @param Schema $schema
     */
    protected function createDiamanteTicketAttachmentsTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_ticket_attachments');
        $table->addColumn('ticket_id', 'integer', []);
        $table->addColumn('attachment_id', 'integer', []);
        $table->setPrimaryKey(['ticket_id', 'attachment_id']);
        $table->addIndex(['ticket_id'], 'IDX_AAF9005E700047D2', []);
        $table->addIndex(['attachment_id'], 'IDX_AAF9005E464E68B', []);
    }

    /**
     * Create diamante_ticket_history table
     *
     * @param Schema $schema
     */
    protected function createDiamanteTicketHistoryTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_ticket_history');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('ticket_id', 'integer', []);
        $table->addColumn('ticket_key', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create diamante_ticket_message_reference table
     *
     * @param Schema $schema
     */
    protected function createDiamanteTicketMessageReferenceTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_ticket_message_ref');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('ticket_id', 'integer', ['notnull' => false]);
        $table->addColumn('message_id', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['ticket_id'], 'IDX_9706F2E3700047D2', []);
    }

    /**
     * Create diamante_ticket_timeline table
     *
     * @param Schema $schema
     */
    protected function createDiamanteTicketTimelineTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_ticket_timeline');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('date', 'datetime', []);
        $table->addColumn('new', 'integer', []);
        $table->addColumn('closed', 'integer', []);
        $table->addColumn('reopen', 'integer', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create diamante_watcher_list table
     *
     * @param Schema $schema
     */
    protected function createDiamanteWatcherListTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_watcher_list');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('ticket_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_type', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['ticket_id'], 'IDX_10274019700047D2', []);
    }

    /**
     * Add diamante_audit foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteAuditForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_audit');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_audit_field foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteAuditFieldForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_audit_field');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_audit'),
            ['audit_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_branch foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteBranchForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_branch');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['default_assignee_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_branch_email_config foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteBranchEmailConfigurationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_branch_email_config');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_branch'),
            ['branch_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_comment foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteCommentForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_comment');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_ticket'),
            ['ticket_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_comment_attachments foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteCommentAttachmentsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_comment_attachments');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_attachment'),
            ['attachment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_comment'),
            ['comment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_ticket foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteTicketForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_ticket');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['assignee_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_branch'),
            ['branch_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_ticket_attachments foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteTicketAttachmentsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_ticket_attachments');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_attachment'),
            ['attachment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_ticket'),
            ['ticket_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_ticket_message_ref foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteTicketMessageReferenceForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_ticket_message_ref');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_ticket'),
            ['ticket_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_watcher_list foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteWatcherListForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_watcher_list');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_ticket'),
            ['ticket_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}