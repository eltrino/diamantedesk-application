<?php

namespace Diamante\DistributionBundle\Installer\Process\Step;

use Oro\Bundle\InstallerBundle\InstallerEvents;
use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Oro\Bundle\InstallerBundle\Process\Step\AbstractStep;

class SchemaStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(600);

        switch ($this->getRequest()->query->get('action')) {
            case 'cache':
                // suppress warning: ini_set(): A session is active. You cannot change the session
                // module's ini settings at this time
                error_reporting(E_ALL ^ E_WARNING);
                return $this->handleAjaxAction('cache:clear', array('--no-optional-warmers' => true));
            case 'clear-config':
                return $this->handleAjaxAction('oro:entity-config:cache:clear', array('--no-warmup' => true));
            case 'clear-extend':
                return $this->handleAjaxAction('oro:entity-extend:cache:clear', array('--no-warmup' => true));
            case 'schema-drop':
                return $this->handleAjaxAction(
                    'doctrine:schema:drop',
                    array('--force' => true, '--full-database' => $context->getStorage()->get('fullDatabase', false))
                );
            case 'schema-update':
                return $this->handleAjaxAction(
                    'oro:migration:load',
                    array('--force' => true, '--exclude' => array('DiamanteEmbeddedFormBundle'), '--timeout' => 0)
                );
            case 'fixtures':
                return $this->handleAjaxAction(
                    'oro:migration:data:load',
                    array('--no-interaction' => true)
                );
            case 'workflows':
                return $this->handleAjaxAction('oro:workflow:definitions:load');
            case 'processes':
                return $this->handleAjaxAction('oro:process:configuration:load');
            case 'diamante-install':
                return $this->handleAjaxAction('diamante:desk:install');
            case 'diamante-front-build':
                return $this->handleAjaxAction('diamante:front:build', array('--with-assets-dependencies' => true));
            case 'diamante-front-assets-install':
                return $this->handleAjaxAction(
                    'oro:assets:install',
                    array('target' => './', '--exclude' => ['OroInstallerBundle'], '--symlink' => true)
                );
            case 'diamante-user-install':
                return $this->handleAjaxAction('diamante:user:install');
            case 'diamante-embeddedform-install':
                return $this->handleAjaxAction('diamante:embeddedform:install');
            case 'navigation':
                return $this->handleAjaxAction('oro:navigation:init');
            case 'js-routing':
                return $this->handleAjaxAction('fos:js-routing:dump', array('--target' => 'js/routes.js'));
            case 'localization':
                return $this->handleAjaxAction('oro:localization:dump');
            case 'translation':
                return $this->handleAjaxAction('oro:translation:dump');
            case 'requirejs':
                return $this->handleAjaxAction('oro:requirejs:build', array('--ignore-errors' => true));
            case 'finish':
                $this->get('event_dispatcher')->dispatch(InstallerEvents::FINISH);
                // everything was fine - update installed flag in parameters.yml
                $dumper = $this->get('oro_installer.yaml_persister');
                $params = $dumper->parse();
                $params['system']['installed'] = date('c');
                $dumper->dump($params);
                // launch 'cache:clear' to set installed flag in DI container
                // suppress warning: ini_set(): A session is active. You cannot change the session
                // module's ini settings at this time
                error_reporting(E_ALL ^ E_WARNING);
                return $this->handleAjaxAction('cache:clear');
        }

        return $this->render('DiamanteDistributionBundle:Process/Step:schema.html.twig');
    }
}
