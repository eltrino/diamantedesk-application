<?php

namespace Diamante\EmbeddedFormBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitBeforeEvent;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;
use Oro\Bundle\EntityExtendBundle\Event\ValueRenderEvent;


use Diamante\DiamanteDeskBundle\Model\BranchAwareInterface;

class EmbeddedFormListener
{
    /** @var Request */
    protected $request;

    /**
     * @param Request|null $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
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

    /**
     * @param EmbeddedFormSubmitBeforeEvent $event
     */
    public function onEmbeddedFormSubmit(EmbeddedFormSubmitBeforeEvent $event)
    {
        /** @var BranchAwareInterface $form */
        $form = $event->getFormEntity();
        /** @var  Object */
        $data = $event->getData();

        if ($data instanceof BranchAwareInterface) {
            $branch = $form->getBranch();
            $data->setBranch($branch);
        }
    }

    /**
     * $param ValueRenderEvent $event
     */
    public function showBranchFieldForTicketType(ValueRenderEvent $event)
    {

    }
}
