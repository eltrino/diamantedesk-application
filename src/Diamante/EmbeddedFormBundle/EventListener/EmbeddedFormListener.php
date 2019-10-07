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
namespace Diamante\EmbeddedFormBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class EmbeddedFormListener
{
    /** @var Request */
    protected $request;

    /**
     * @param RequestStack|null $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Add owner field to forms
     *
     * @param BeforeFormRenderEvent $event
     */
    public function addBranchField(BeforeFormRenderEvent $event)
    {
        if (!$this->request) {
            return;
        }

        $routename = $this->request->attributes->get('_route');

        if (strrpos($routename, 'oro_embedded_form_') === 0) {
            $env              = $event->getTwigEnvironment();
            $data             = $event->getFormData();
            $form             = $event->getForm();
            $branchField = $env->render('DiamanteEmbeddedFormBundle:Form:branchField.html.twig', ['form' => $form]);

            /**
             * Setting branch field as last field in first data block
             */
            if (!empty($data['dataBlocks'])) {
                if (isset($data['dataBlocks'][0]['subblocks'])) {
                    array_splice($data['dataBlocks'][0]['subblocks'][0]['data'], 1, 0, $branchField);
                }
            }

            $event->setFormData($data);
        }
    }

}
