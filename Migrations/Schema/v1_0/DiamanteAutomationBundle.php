<?php

namespace Diamante\AutomationBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;


/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
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
        $this->createDiamanteBusinessRuleTable($schema);
        $this->createDiamanteRuleActionTable($schema);
        $this->createDiamanteRuleConditionTable($schema);
        $this->createDiamanteRuleGroupTable($schema);
        $this->createDiamanteWorkflowRuleTable($schema);

        /** Foreign keys generation **/
        $this->addDiamanteBusinessRuleForeignKeys($schema);
        $this->addDiamanteRuleActionForeignKeys($schema);
        $this->addDiamanteRuleConditionForeignKeys($schema);
        $this->addDiamanteRuleGroupForeignKeys($schema);
        $this->addDiamanteWorkflowRuleForeignKeys($schema);
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
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['root_group_id'], 'UNIQ_5182F28A8509B3A1');
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
        $table->addColumn('parameters', 'string', ['length' => 255]);
        $table->addColumn('weight', 'integer', []);
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
        $table->addColumn('parameters', 'string', ['length' => 255]);
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
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add diamante_rule_action foreign keys.
     *
     * @param Schema $schema
     */
    protected function addDiamanteRuleActionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('diamante_rule_action');
        $table->addForeignKeyConstraint(
            $schema->getTable('diamante_workflow_rule'),
            ['rule_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
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
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}

