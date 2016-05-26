<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\Twig\Extensions;


use Diamante\DeskBundle\Entity\Branch;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class BranchExtension extends \Twig_Extension
{
    /**
     * @var ConfigManager
     */
    protected $manager;

    public function __construct(ConfigManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return 'diamante_branch_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("is_default_branch", [$this, 'isDefaultBranch', ['is_safe' => ['html']]])
        ];
    }

    public function isDefaultBranch(Branch $branch)
    {
        $defaultBranchId = $this->manager->get('diamante_desk.default_branch');

        if (empty($defaultBranchId)) {
            return false;
        }

        return (int)$defaultBranchId === $branch->getId();
    }
}