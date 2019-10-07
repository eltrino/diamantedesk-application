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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Diamante\DeskBundle\Api\BranchService;

class DeleteBranch extends AbstractType
{
    /**
     * @var BranchService
     */
    private $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();
        foreach ($this->branchService->getAllbranches() as $branch) {
            $choices[$branch->getId()] = $branch->getName();
        }

        $brunchForDelete = $options['data']['id'];
        if (isset($choices[$brunchForDelete])) {
            unset($choices[$brunchForDelete]);
        }

        $builder->add(
            $builder->create(
                'newBranch',
                ChoiceType::class,
                array(
                    'label' => 'diamante.desk.branch.messages.delete.select',
                    'required' => true,
                    'attr' => array('style' => 'width:110px'),
                    'choices' => array_flip($choices)
                )
            )
        )->add(
            $builder->create(
                'moveTickets',
                CheckboxType::class,
                array(
                    'label' => 'diamante.desk.branch.messages.delete.move',
                    'required' => false,
                )
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
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'diamante_delete_branch_form';
    }
}
