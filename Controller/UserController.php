<?php

namespace Diamante\UserBundle\Controller;

use Diamante\DeskBundle\Controller\Shared\ExceptionHandlerTrait;
use Diamante\DeskBundle\Controller\Shared\FormHandlerTrait;
use Diamante\DeskBundle\Controller\Shared\ResponseHandlerTrait;
use Diamante\DeskBundle\Controller\Shared\SessionFlashMessengerTrait;
use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Api\Command\UpdateDiamanteUserCommand;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Model\User;
use JMS\AopBundle\Exception\RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class UserController extends Controller
{
    use FormHandlerTrait;
    use ExceptionHandlerTrait;
    use SessionFlashMessengerTrait;
    use ResponseHandlerTrait;

    /**
     * @Route("/", name="diamante_user_list")
     * @Template()
     */
    public function listAction()
    {
        return [];
    }

    /**
     * @param $id
     * @Route("/view/{id}", name="diamante_user_view", requirements={"id" = "\d+"})
     * @Template()
     *
     * @return array
     */
    public function viewAction($id)
    {
        $user = $this->container->get('doctrine')
            ->getManager()
            ->getRepository('DiamanteUserBundle:DiamanteUser')
            ->get($id);

        return ['entity' => $user];
    }

    /**
     * @Route("/create", name="diamante_user_create")
     * @Template()
     */
    public function createAction()
    {
        $command = new CreateDiamanteUserCommand();
        try {
            $form = $this->createForm('diamante_user_create', $command);
            $result = $this->edit($command, $form, function ($command){
                $userId = $this->get('diamante.user.service')->createDiamanteUser($command);
                return $userId;
            });

            return $result;
        } catch (\RuntimeException $e) {
            $this->handleException($e);
            return $this->redirect('diamante_user_list');
        }
    }

    /**
     * @param $id
     *
     * @Route("/update/{id}", name="diamante_user_update", requirements={"id" = "\d+"})
     * @Template()
     *
     * @return array|null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction($id)
    {
        $command = new UpdateDiamanteUserCommand();
        /** @var DiamanteUser $user */
        $user = $this->get('doctrine')
            ->getManager()
            ->getRepository('DiamanteUserBundle:DiamanteUser')
            ->get($id);

        $command->id = $id;
        $command->email = $user->getEmail();
        $command->lastName = $user->getLastName();
        $command->firstName = $user->getFirstName();

        try {
            $form = $this->createForm('diamante_user_update', $command);
            $result = $this->edit($command, $form, function ($command){
                $userId = $this->get('diamante.user.service')->updateDiamanteUser($command);
                return $userId;
            });

            return $result;
        } catch (\Exception $e) {
            $this->handleException($e);
            return $this->redirect($this->get('router')->generate('diamante_user_update', ['id' => $id]));
        }
    }

    /**
     * @param $id
     *
     * @Route("/delete/{id}", name="diamante_user_delete", requirements={"id" = "\d+"})
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        try {
            if (!in_array($this->get('request')->getMethod(), ['POST', 'PUT'])) {
                throw new MethodNotAllowedException("This won't work");
            }

            $this->get('diamante.user.service')
                ->removeDiamanteUser($id);
            return new Response(null, 204, array(
                'Content-Type' => $this->getRequest()->getMimeType('json')
            ));
        } catch (\Exception $e) {
            $this->handleException($e);
            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @param $command
     * @param Form $form
     * @param $callback
     * @return array|null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function edit($command, Form $form, $callback)
    {
        $response = null;

        try {
            $this->handle($form);
            $userId = $callback($command);

            if (isset($command->id) && ($command->id !== null)) {
                $this->addSuccessMessage('diamante.user.messages.update.success');
            } else {
                $this->addSuccessMessage('diamante.user.messages.create.success');
            }

            $response = $this->getSuccessSaveResponse('diamante_user_update', 'diamante_user_view', ['id' => $userId]);
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = ['form' => $form->createView()];
        }

        return $response;
    }

    /**
     * @Route("/delete/massaction", name="diamante_user_delete_massaction", options={"expose"=true})
     */
    public function massRemoveAction()
    {
        $users = $this->get('request')->get('values');

        if (!is_array($users)) {
            $users = explode(',', $users);
        }

        try {
            foreach ($users as $user) {
                $this->get('diamante.user.service')->removeDiamanteUser($user);
            }

            $response = $this->getMassActionResponse('delete', 'user', true);

        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getMassActionResponse('delete', 'user', false);
        }

        return $response;
    }


    /**
     * @param $id
     *
     * @Route("/reset/{id}", name="diamante_user_force_reset", options={"expose"=true}, requirements={"id"="\d+"});
     *
     * @return Response
     */
    public function resetPasswordAction($id)
    {
        $user = new User($id, User::TYPE_DIAMANTE);

        try {
            $this->get('diamante.user.service')->resetPassword($user);
            return new Response(null, 204, array(
                'Content-Type' => $this->getRequest()->getMimeType('json')
            ));
        } catch (\Exception $e) {
            $this->handleException($e);
            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @Route("/reset/massaction", name="diamante_user_reset_pwd_massaction", options={"expose" = true})
     */
    public function massResetPasswordAction()
    {
        $users = $this->get('request')->get('values');

        if (!is_array($users)) {
            $users = explode(',', $users);
        }

        try {
            foreach ($users as $user) {
                $this->get('diamante.user.service')->resetPassword(new User($user, User::TYPE_DIAMANTE));
            }

            $response = $this->getMassActionResponse('reset_pwd', 'user', true);
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = $this->getMassActionResponse('reset_pwd', 'user', false);
        }

        return $response;
    }
}
