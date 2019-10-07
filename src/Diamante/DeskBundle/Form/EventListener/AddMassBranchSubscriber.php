<?php

namespace Diamante\DeskBundle\Form\EventListener;

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
use Diamante\DeskBundle\Api\BranchService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class AddMassBranchSubscriber
 *
 * @package Diamante\DeskBundle\Form\EventListener
 */
class AddMassBranchSubscriber implements EventSubscriberInterface
{
    /**
     * @var BranchService
     */
    private $branchService;

    /**
     * AddMassBranchSubscriber constructor.
     *
     * @param BranchService $branchService
     */
    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $removeBranchList = $event->getData()['values'];
        $form = $event->getForm();

        $choices = [];
        foreach ($this->branchService->getAllbranches() as $branch) {
            $choices[$branch->getId()] = $branch->getName();
        }

        if (isset($removeBranchList)) {
            $removeBranches = explode(',', $removeBranchList);
            $flipedBranches = array_flip($removeBranches);
            $choices = array_diff_key($choices, $flipedBranches);
        }

        $form->add(
            'newBranch',
            ChoiceType::class,
            [
                'label'    => 'diamante.desk.branch.messages.delete.select',
                'required' => true,
                'attr'     => ['style' => 'width:110px'],
                'choices'  => $choices
            ]
        )->add(
            'removeBranches',
            HiddenType::class,
            [
                'required' => false,
                'data'     => $removeBranchList
            ]
        );
    }
}