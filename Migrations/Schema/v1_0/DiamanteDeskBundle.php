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
        if (!$this->isExecutedFromInstallCommand()) {
            return;
        }
        $this->auditFieldExtension->addType($schema, $doctrineType = 'status', $auditType = 'status');
        $this->auditFieldExtension->addType($schema, $doctrineType = 'priority', $auditType = 'priority');
        $this->auditFieldExtension->addType($schema, $doctrineType = 'user_type', $auditType = 'user_type');
        $this->auditFieldExtension->addType($schema, $doctrineType = 'file', $auditType = 'file');
    }

    /**
     * @return bool
     */
    private function isExecutedFromInstallCommand()
    {
        $request = Request::createFromGlobals();
        $args = $request->server->get('argv');
        if (!is_array($args)) {
            echo "web installer";
            // Executed from diamantedesk-application
            return true;
        } elseif (isset($args[1])) {
            echo "console installer";
            // Executed from install command
            return $args[1] === 'diamante:install';
        }

        // Executed from oro:migration:load
        return false;
    }
}
