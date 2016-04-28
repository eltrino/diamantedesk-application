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

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\InstallerBundle\Process\Step\AbstractStep;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class SetupStep extends AbstractStep
{
    const ORGANIZATION_NAME = 'DiamanteDesk';

    public function displayAction(ProcessContextInterface $context)
    {
        $form = $this->createForm('diamante_installer_setup');

        $form->get('organization_name')->setData('DiamanteDesk');
        $form->get('application_url')->setData('http://localhost/diamantedesk');

        return $this->render(
            'DiamanteDistributionBundle:Process/Step:setup.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {
        $adminUser = $this
            ->getDoctrine()
            ->getRepository('OroUserBundle:User')
            ->findOneBy(array('username' => LoadAdminUserData::DEFAULT_ADMIN_USERNAME));

        if (!$adminUser) {
            throw new \RuntimeException("Admin user wasn't loaded in fixtures.");
        }

        $form = $this->createForm('diamante_installer_setup');
        $form->setData($adminUser);

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $context->getStorage()->set(
                'loadFixtures',
                false
            );

            $this->get('oro_user.manager')->updateUser($adminUser);

            /** @var ConfigManager $configManager */
            $configManager           = $this->get('oro_config.global');
            $defaultOrganizationName = $configManager->get('oro_ui.organization_name');
            $organizationName        = $form->get('organization_name')->getData();
            if (!empty($organizationName) && $organizationName !== $defaultOrganizationName) {
                $configManager->set('oro_ui.application_name', $organizationName);
            }

            $defaultAppURL       = $configManager->get('oro_ui.application_url');
            $applicationURL      = $form->get('application_url')->getData();
            if (!empty($applicationURL) && $applicationURL !== $defaultAppURL) {
                $configManager->set('oro_ui.application_url', $applicationURL);
            }
            $configManager->flush();

            $this->runCommand('diamante:desk:data');
            $this->runCommand('oro:migration:data:load', ['--bundles' => ['DiamanteDistributionBundle']]);

            return $this->complete();
        }

        return $this->render(
            'DiamanteDistributionBundle:Process/Step:setup.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }
}
