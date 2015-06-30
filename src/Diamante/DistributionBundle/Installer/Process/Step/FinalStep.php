<?php

namespace Diamante\DistributionBundle\Installer\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Oro\Bundle\InstallerBundle\Process\Step\AbstractStep;

class FinalStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        $this->complete();

        return $this->render('DiamanteDistributionBundle:Process/Step:final.html.twig');
    }
}
