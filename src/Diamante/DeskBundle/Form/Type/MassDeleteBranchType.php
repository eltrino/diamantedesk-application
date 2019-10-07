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
use Symfony\Component\Form\FormBuilderInterface;
use Diamante\DeskBundle\Form\EventListener\AddMassBranchSubscriber;

class MassDeleteBranchType extends AbstractType
{
    /**
     * @var AddMassBranchSubscriber
     */
    private $massBranchSubscriber;

    /**
     * MassDeleteBranchType constructor.
     *
     * @param AddMassBranchSubscriber $massBranchSubscriber
     */
    public function __construct(AddMassBranchSubscriber $massBranchSubscriber)
    {
        $this->massBranchSubscriber = $massBranchSubscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create(
                'moveMassTickets',
                CheckboxType::class,
                [
                    'label'    => 'diamante.desk.branch.messages.delete.move',
                    'required' => false,
                ]
            )
        )->addEventSubscriber($this->massBranchSubscriber);
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
        return 'diamante_mass_delete_branch_form';
    }
}
