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
use Symfony\Component\HttpFoundation\Response;

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
            $tickets = $this->get('doctrine')
                ->getManager()
                ->getRepository('DiamanteDeskBundle:Ticket')
                ->count(['reporter', 'eq', sprintf("%s_%d", User::TYPE_DIAMANTE, $id)]);

            if ($tickets > 0) {
                throw new RuntimeException('User has existing tickets. User can not be deleted');
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
}
