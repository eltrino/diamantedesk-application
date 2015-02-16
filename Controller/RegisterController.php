<?php

namespace Diamante\FrontBundle\Controller;

use Diamante\FrontBundle\Api\Command\ConfirmCommand;
use Diamante\FrontBundle\Api\Command\RegisterCommand;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Post;

/**
 * @RouteResource("user")
 * @NamePrefix("diamante_front_api_")
 */
class RegisterController extends FOSRestController
{
    /**
     * Register new Diamante User
     *
     * @Post
     * @ApiDoc(
     *      description="Register (create) new Diamante User",
     *      resource=true
     * )
     */
    public function registerAction()
    {
        $command = new RegisterCommand();
        $command->username = $this->getRequest()->get('username');
        $command->email = $this->getRequest()->get('email');
        $command->password = $this->getRequest()->get('password');
        $command->firstname = $this->getRequest()->get('firstname');
        $command->lastname = $this->getRequest()->get('lastname');

        try {
            $this->get('diamante.front.registration.service')->register($command);
            $view = $this->view(null, Codes::HTTP_CREATED);
        } catch (\Exception $e) {
            $view = $this->view(null, Codes::HTTP_BAD_REQUEST);
        }
        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Confirm new Diamante User registration
     *
     * @Post
     * @ApiDoc(
     *      description="Confirm new Diamante User registration",
     *      resource=true
     * )
     */
    public function confirmAction()
    {
        $command = new ConfirmCommand();
        $command->email = $this->getRequest()->get('email');
        $command->activationHash = $this->getRequest()->get('activation_hash');

        try {
            $this->get('diamante.front.registration.service')->confirm($command);
            $view = $this->view(null, Codes::HTTP_CREATED);
        } catch (\Exception $e) {
            $view = $this->view(null, Codes::HTTP_BAD_REQUEST);
        }
        return $this->get('fos_rest.view_handler')->handle($view);
    }
}
