<?php

namespace Diamante\AutomationBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Class DiamanteAutomationBundle
 *
 * @package Diamante\AutomationBundle\Migrations\Schema\v1_0
 */
class DiamanteAutomationBundle implements Installation
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
        $this->createDiamanteCronScheduleTable($schema);
        $this->createDiamanteEventTriggeredRuleTable($schema);
        $this->createDiamanteRuleActionTable($schema);
        $this->createDiamanteRuleConditionTable($schema);
        $this->createDiamanteRuleGroupTable($schema);
        $this->createDiamanteTimeTriggeredRuleTable($schema);

        /** Foreign keys generation **/
        $this->addDiamanteEventTriggeredRuleForeignKeys($schema);
        $this->addDiamanteRuleConditionForeignKeys($schema);
        $this->addDiamanteRuleGroupForeignKeys($schema);
        $this->addDiamanteTimeTriggeredRuleForeignKeys($schema);
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
     * Create diamante_event_triggered_rule table
     *
     * @param Schema $schema
     */
    protected function createDiamanteEventTriggeredRuleTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_event_triggered_rule');
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('root_group_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('status', 'boolean', []);
        $table->addColumn('target', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['root_group_id'], 'UNIQ_58F8EF2C8509B3A1');
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
        $table->addColumn('discr', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['parent_id'], 'IDX_5E6DB1A6727ACA70', []);
    }

    /**
     * Create diamante_time_triggered_rule table
     *
     * @param Schema $schema
     */
    protected function createDiamanteTimeTriggeredRuleTable(Schema $schema)
    {
        $table = $schema->createTable('diamante_time_triggered_rule');
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('root_group_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('time_interval', 'string', ['length' => 255]);
        $table->addColumn('status', 'boolean', []);
        $table->addColumn('target', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['root_group_id'], 'UNIQ_369D941F8509B3A1');
    }

    /**
     * Add diamante_event_triggered_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteEventTriggeredRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_event_triggered_rule');
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
     * Add diamante_time_triggered_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteTimeTriggeredRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_time_triggered_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_rule_group'),
            ['root_group_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
