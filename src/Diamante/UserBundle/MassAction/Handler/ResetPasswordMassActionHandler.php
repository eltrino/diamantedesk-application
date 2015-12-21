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

namespace Diamante\UserBundle\MassAction\Handler;


use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ResetPasswordMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var string
     */
    protected $responseMessage = 'diamante.user.actions.mass.reset_pwd.messages.success';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var UserService
     */
    protected $userService;

    public function __construct(
        TranslatorInterface $translator,
        UserService $userService
    )
    {
        $this->translator   = $translator;
        $this->userService  = $userService;
    }

    public function handle(MassActionHandlerArgs $args)
    {
        $iterations = 0;

        try {
            /** @var ResultRecord $result */
            foreach ($args->getResults() as $result) {
                $this->userService->resetPassword(new User($result->getValue('id'), User::TYPE_DIAMANTE));
                ++$iterations;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->getResponse($args, $iterations);
    }

    protected function getResponse(MassActionHandlerArgs $args, $count = 0)
    {
        $massAction      = $args->getMassAction();
        $responseMessage = $massAction->getOptions()->offsetGetByPath('[messages][success]', $this->responseMessage);

        $successful = $count > 0;
        $options    = ['count' => $count];

        return new MassActionResponse(
            $successful,
            $this->translator->transChoice(
                $responseMessage,
                $count,
                ['%count%' => $count]
            ),
            $options
        );
    }
}