<?php

namespace Diamante\DistributionBundle\Installer\Process\Step;

use Oro\Bundle\InstallerBundle\InstallerEvents;
use Oro\Bundle\SecurityBundle\Command\LoadPermissionConfigurationCommand;
use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Oro\Bundle\InstallerBundle\Process\Step\AbstractStep;
use Symfony\Component\HttpFoundation\JsonResponse;

class InitializationStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(900);

        switch ($this->getRequest()->query->get('action')) {
            case 'cache':
                // suppress warning: ini_set(): A session is active. You cannot change the session
                // module's ini settings at this time
                error_reporting(E_ALL ^ E_WARNING);
                return $this->handleAjaxAction('cache:clear', ['--no-optional-warmers' => true]);
            case 'schema-drop':
                return $this->handleAjaxAction(
                    'doctrine:schema:drop',
                    ['--force' => true, '--full-database' => $context->getStorage()->get('dropDatabase', false)]
                );
            case 'schema-update':
                return $this->handleSchemaUpdate();
            case 'workflows':
                return $this->handleAjaxAction('oro:workflow:definitions:load');
            case 'permissions':
                return $this->handleAjaxAction(LoadPermissionConfigurationCommand::NAME);
            case 'crons':
                return $this->handleAjaxAction('oro:cron:definitions:load');
            case 'processes':
                return $this->handleAjaxAction('oro:process:configuration:load');
            case 'fixtures':
                return $this->handleFixtures();
            case 'js-routing':
                return $this->handleAjaxAction('fos:js-routing:dump');
            case 'localization':
                return $this->handleAjaxAction('oro:localization:dump');
            case 'assets':
                return $this->handleAjaxAction(
                    'oro:assets:install',
                    array('target' => './', '--symlink', '--exclude' => ['OroInstallerBundle'])
                );
            case 'assetic':
                return $this->handleAjaxAction('assetic:dump');
            case 'translation':
                return $this->handleAjaxAction('oro:translation:dump');
            case 'requirejs':
                return $this->handleAjaxAction('oro:requirejs:build', ['--ignore-errors' => true]);
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

        return $this->render('DiamanteDistributionBundle:Process/Step:initialization.html.twig');
    }

    protected function handleSchemaUpdate()
    {
        $actions = [
            ['oro:migration:load', ['--force' => true, '--timeout' => 0]]
        ];

        $exitCode = 0;
        foreach ($actions as $action) {
            list($command, $params) = $action;
            $this->container->set('oro_migration.migrations.loader', null);
            $exitCode = $this->runCommand($command, $params);
            if ($exitCode) {
                return $this->handleResponse($exitCode);
            }
        }
        return $this->handleResponse($exitCode);
    }

    protected function handleFixtures()
    {
        $actions = [
            ['oro:migration:data:load', ['--no-interaction' => true, '--exclude' => ['DiamanteDistributionBundle']]],
        ];

        $exitCode = 0;
        foreach ($actions as $action) {
            list($command, $params) = $action;
            $exitCode = $this->runCommand($command, $params);
            if ($exitCode) {
                return $this->handleResponse($exitCode);
            }
        }
        return $this->handleResponse($exitCode);
    }

    /**
     * @param int $exitCode
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function handleResponse($exitCode)
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse(['result' => true, 'exitCode' => $exitCode]);
        } else {
            return $this->redirect(
                $this->generateUrl(
                    'sylius_flow_display',
                    [
                        'scenarioAlias' => 'oro_installer',
                        'stepName'      => $this->getName(),
                    ]
                )
            );
        }
    }
}
