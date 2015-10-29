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
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\HttpFoundation\Request;

class DiamanteDeskBundle implements Migration, AuditFieldExtensionAwareInterface
{
    /** @var AuditFieldExtension */
    private $auditFieldExtension;

    public function setAuditFieldExtension(AuditFieldExtension $extension)
    {
        $this->auditFieldExtension = $extension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $types = [
            'status', 'priority', 'user_type', 'attachment_file', 'source'
        ];

        foreach ($types as $type) {
            if (!$this->auditFieldTypeExists($schema, $type)) {
                $this->auditFieldExtension->addType($schema, $type, $type);
            }
        }
    }

    private function auditFieldTypeExists(Schema $schema, $type)
    {
        $table = $schema->getTable('oro_audit_field');

        return ($table->hasColumn(sprintf("old_%s", $type)) && $table->hasColumn(sprintf("new_%", $type)));
    }
}
