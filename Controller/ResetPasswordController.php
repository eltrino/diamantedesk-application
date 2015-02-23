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
namespace Diamante\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;
use Diamante\FrontBundle\Api\Command\ChangePasswordCommand;
use Diamante\FrontBundle\Api\Command\ResetPasswordCommand;

class ResetPasswordController extends Controller
{
    /**
     * Reset password action
     * @Post("/password/reset", name="diamante_front_reset_password")
     */
    public function resetAction(Request $request)
    {
        $command = new ResetPasswordCommand();
        $command->email = $request->get('email');
        try {
            $resetService = $this->container->get('diamante.front.reset_password');
            $resetService->resetPassword($command);
        } catch(\Exception $e) {
            return new Response($e->getMessage(), 404);
        }

        return new Response();
    }

    /**
     * Update password action
     * @Post("password/update", name="diamante_front_update_password")
     */
    public function updateAction(Request $request)
    {
        // сделать логирование как в оро контроллерах при ексепшене
        $command = new ChangePasswordCommand();
        $command->hash = $request->get('hash');
        $command->password = $request->get('password');
        try {
            $resetService = $this->container->get('diamante.front.reset_password');
            $resetService->changePassword($command);
        } catch(\Exception $e) {
            return new Response($e->getMessage(), 404);
        }

        return new Response();
    }
}
