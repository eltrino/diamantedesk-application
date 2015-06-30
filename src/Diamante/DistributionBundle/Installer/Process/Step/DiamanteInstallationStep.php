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

namespace Diamante\DistributionBundle\Installer\Process\Step;

use Oro\Bundle\InstallerBundle\CommandExecutor;
use Oro\Bundle\InstallerBundle\InstallerEvents;
use Oro\Bundle\InstallerBundle\Process\Step\AbstractStep;
use Oro\Bundle\InstallerBundle\ScriptExecutor;
use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DiamanteInstallationStep extends AbstractStep
{
    /**
     * Display action.
     *
     * @param ProcessContextInterface $context
     *
     * @return Response
     */
    public function displayAction(ProcessContextInterface $context)
    {
        $action = $this->getRequest()->query->get('action');
        switch ($action) {
            case 'diamante-install':
                return $this->handleAjaxAction('diamante:desk:install');
            case 'diamante-front-build':
                return $this->handleAjaxAction('diamante:front:build', array('--with-assets-dependencies'));
            case 'diamante-front-assets-install':
                return $this->handleAjaxAction(
                    'oro:assets:install',
                    array('target' => './', '--exclude' => ['OroInstallerBundle'])
                );
            case 'diamante-user-install':
                return $this->handleAjaxAction('diamante:user:install');
            case 'fixtures':
                return $this->handleAjaxAction('oro:migration:data:load', array('--fixtures-type' => 'demo'));
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

        // check if we have package installation step
        if (strpos($action, 'installerScript-') !== false) {
            $scriptFile = $this->container->get('oro_installer.script_manager')->getScriptFileByKey(
                str_replace('installerScript-', '', $action)
            );

            $scriptExecutor = new ScriptExecutor(
                $this->getOutput(),
                $this->container,
                new CommandExecutor(
                    $this->container->getParameter('kernel.environment'),
                    $this->getOutput(),
                    $this->getApplication()
                )
            );
            $scriptExecutor->runScript($scriptFile);

            return new JsonResponse(array('result' => true));
        }

        return $this->render('DiamanteDistributionBundle:Process/Step:diamanteInstallation.html.twig',
            array(
                'loadFixtures' => $context->getStorage()->get('loadFixtures'),
                'installerScripts' => $this
                    ->container
                    ->get('oro_installer.script_manager')
                    ->getScriptLabels(),
            )
        );
    }

    /**
     * @return array list of registered bundles
     */
    private function listBundlesToExcludeInAssetsInstall()
    {
        $bundles = $this->container->getParameter('kernel.bundles');
        if (isset($bundles['DiamanteFrontBundle'])) {
            unset($bundles['DiamanteFrontBundle']);
        }
        return array_keys($bundles);
    }
}
