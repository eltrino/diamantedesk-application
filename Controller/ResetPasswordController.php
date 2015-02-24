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

use Symfony\Component\HttpFoundation\Response;
use Diamante\FrontBundle\Api\Command\ChangePasswordCommand;
use Diamante\FrontBundle\Api\Command\ResetPasswordCommand;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use FOS\Rest\Util\Codes;

/**
 * @RouteResource("password")
 * @NamePrefix("diamante_front_api_")
 */
class ResetPasswordController extends FOSRestController
{
    /**
     * Reset user password action
     *
     * @Post
     * @ApiDoc(
     *      description="Reset user password action",
     *      resource=true
     * )
     */
    public function resetAction()
    {
        $command = new ResetPasswordCommand();
        $command->email = $this->getRequest()->get('email');
        try {
            $resetService = $this->container->get('diamante.front.reset_password');
            $resetService->resetPassword($command);
            $view = $this->view(null, Codes::HTTP_OK);
        } catch(\Exception $e) {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        }
        return $this->response($view);
    }

    /**
     * Update user password action
     *
     * @Post
     * @ApiDoc(
     *      description="Update user password action",
     *      resource=true
     * )
     */
    public function updateAction()
    {
        $command = new ChangePasswordCommand();
        $command->hash = $this->getRequest()->get('hash');
        $command->password = $this->getRequest()->get('password');
        try {
            $resetService = $this->container->get('diamante.front.reset_password');
            $resetService->changePassword($command);
            $view = $this->view(null, Codes::HTTP_OK);
        } catch(\Exception $e) {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        }
        return $this->response($view);
    }

    /**
     * @param View $view
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function response(View $view)
    {
        return $this->get('fos_rest.view_handler')->handle($view);
    }
}
