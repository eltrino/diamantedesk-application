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

namespace Diamante\AutomationBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class DiamanteAutomationBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createDiamanteAutomationContextTable($schema);
        $this->createDiamanteBusinessRuleTable($schema);
        $this->createDiamanteCronScheduleTable($schema);
        $this->createDiamanteRuleActionTable($schema);
        $this->createDiamanteRuleConditionTable($schema);
        $this->createDiamanteRuleGroupTable($schema);
        $this->createDiamanteWorkflowRuleTable($schema);

        /** Foreign keys generation **/
        $this->addDiamanteBusinessRuleForeignKeys($schema);
        $this->addDiamanteRuleConditionForeignKeys($schema);
        $this->addDiamanteRuleGroupForeignKeys($schema);
        $this->addDiamanteWorkflowRuleForeignKeys($schema);
    }

    /**
     * Create diamante_automation_context table
     *
     * @param Schema $schema
     */
    protected function createDiamanteAutomationContextTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_automation_context');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('targetEntityId', 'integer', ['notnull' => false]);
        $table->addColumn('targetEntityClass', 'string', ['length' => 255]);
        $table->addColumn('action', 'string', ['length' => 255]);
        $table->addColumn('targetEntityChangeset', 'array', ['comment' => '(DC2Type:array)']);
        $table->addColumn('state', 'string', ['length' => 255]);
        $table->addColumn('editor_id', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create diamante_business_rule table
     *
     * @param Schema $schema
     */
    protected function createDiamanteBusinessRuleTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_business_rule');
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('root_group_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('time_interval', 'string', ['length' => 255]);
        $table->addColumn('status', 'boolean', []);
        $table->addColumn('target', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['root_group_id'], 'UNIQ_5182F28A8509B3A1');
    }

    /**
     * Create diamante_cron_schedule table
     *
     * @param Schema $schema
     */
    protected function createDiamanteCronScheduleTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_cron_schedule');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('command', 'string', ['length' => 255]);
        $table->addColumn('parameters', 'array', ['comment' => '(DC2Type:array)']);
        $table->addColumn('definition', 'string', ['notnull' => false, 'length' => 100]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create diamante_rule_action table
     *
     * @param Schema $schema
     */
    protected function createDiamanteRuleActionTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_rule_action');
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('rule_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('parameters', 'array', ['comment' => '(DC2Type:array)']);
        $table->addColumn('weight', 'integer', []);
        $table->addColumn('discr', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['rule_id'], 'IDX_93441185744E0351', []);
    }

    /**
     * Create diamante_rule_condition table
     *
     * @param Schema $schema
     */
    protected function createDiamanteRuleConditionTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_rule_condition');
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('group_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('parameters', 'array', ['comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['group_id'], 'IDX_8333C227FE54D947', []);
    }

    /**
     * Create diamante_rule_group table
     *
     * @param Schema $schema
     */
    protected function createDiamanteRuleGroupTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_rule_group');
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('parent_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('connector', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['parent_id'], 'IDX_5E6DB1A6727ACA70', []);
    }

    /**
     * Create diamante_workflow_rule table
     *
     * @param Schema $schema
     */
    protected function createDiamanteWorkflowRuleTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_workflow_rule');
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('root_group_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('status', 'boolean', []);
        $table->addColumn('target', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['root_group_id'], 'UNIQ_4486E0BC8509B3A1');
    }

    /**
     * Add diamante_business_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteBusinessRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_business_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_rule_group'),
            ['root_group_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_rule_condition foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteRuleConditionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_rule_condition');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_rule_group'),
            ['group_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_rule_group foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteRuleGroupForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_rule_group');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_rule_group'),
            ['parent_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_workflow_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteWorkflowRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_workflow_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_rule_group'),
            ['root_group_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
