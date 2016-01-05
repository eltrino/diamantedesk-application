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
namespace Diamante\DeskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Diamante\DeskBundle\Form\DataTransformer\StatusTransformer;
use Diamante\DeskBundle\Api\BranchService;

class MassDeleteBranchType extends AbstractType
{
    /**
     * @var BranchService
     */
    private $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach ($this->branchService->getAllbranches() as $branch) {
            $choices[$branch->getId()] = $branch->getName();
        }

        if (isset($options['data']['values'])) {
            $removeBranches = explode(',', $options['data']['values']);
            $flipedBranches = array_flip($removeBranches);
            $choices = array_diff_key($choices, $flipedBranches);
        }

        $builder->add(
            $builder->create(
                'newBranch',
                'choice',
                [
                    'label'    => 'diamante.desk.branch.messages.delete.select',
                    'required' => true,
                    'attr'     => ['style' => "width:110px"],
                    'choices'  => $choices
                ]
            )
        )->add(
            $builder->create(
                'moveMassTickets',
                'checkbox',
                [
                    'label'    => 'diamante.desk.branch.messages.delete.move',
                    'required' => false,
                ]
            )
        )->add(
            $builder->create(
                'removeBranches',
                'hidden',
                [
                    'required' => false,
                    'data' => $options['data']['values']
                ]
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'diamante_mass_delete_branch_form';
    }
}
