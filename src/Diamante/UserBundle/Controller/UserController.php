<?php

namespace Diamante\UserBundle\Controller;

use Diamante\DeskBundle\Controller\Shared;
use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Api\Command\UpdateDiamanteUserCommand;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Exception\UserRemovalException;
use Diamante\UserBundle\Form\Type\CreateDiamanteUserType;
use Diamante\UserBundle\Form\Type\UpdateDiamanteUserType;
use Diamante\UserBundle\Model\User;
use JMS\AopBundle\Exception\RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class UserController extends Controller
{
    use Shared\FormHandlerTrait;
    use Shared\ExceptionHandlerTrait;
    use Shared\SessionFlashMessengerTrait;
    use Shared\ResponseHandlerTrait;
    use Shared\RequestGetterTrait;

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
    public function createAction(Request $request)
    {
        $command = new CreateDiamanteUserCommand();
        try {
            $form = $this->createForm(CreateDiamanteUserType::class, $command);
            $result = $this->edit($request, $command, $form, function($command) {
                $userId = $this->get('diamante.user.service')->createDiamanteUser($command);
                return $userId;
            });

            return $result;
        } catch (\RuntimeException $e) {
            $this->handleException($e);
            return $this->redirect(
                $this->generateUrl(
                    'diamante_user_create'
                )
            );
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
    public function updateAction(Request $request, $id)
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
            $form = $this->createForm(UpdateDiamanteUserType::class, $command);
            $result = $this->edit($request, $command, $form, function($command) {
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
    public function deleteAction(Request $request, $id)
    {
        try {
            if (!in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
                throw new MethodNotAllowedException("This won't work");
            }

            $this->ensureUserHasNoRelatedEntities($id);

            $this->get('diamante.user.service')
                ->removeDiamanteUser($id);
            return new Response(null, 204, array(
                'Content-Type' => $request->getMimeType('json')
            ));
        } catch (\Exception $e) {
            $this->handleException($e);
            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @param CreateDiamanteUserCommand $command
     * @param Form $form
     * @param \Closure $callback
     * @return array|null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function edit(Request $request, $command, Form $form, $callback)
    {
        $response = null;

        try {
            $this->handle($request, $form);
            $userId = $callback($command);

            if (isset($command->id) && ($command->id !== null)) {
                $this->addSuccessMessage('diamante.user.messages.update.success');
            } else {
                $this->addSuccessMessage('diamante.user.messages.create.success');
            }
            $response = $this->getSuccessSaveResponse(
                'diamante_user_update',
                'diamante_user_view',
                ['id' => $userId]
            );
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = ['form' => $form->createView()];
        }

        return $response;
    }

    /**
     * @Route("/delete/massaction", name="diamante_user_delete_massaction", options={"expose"=true})
     */
    public function massRemoveAction(Request $request)
    {
        $params = $this->parseGridParameters($request);
        $repository = $this->get('diamante.user.repository');
        $users = $repository->findByDataGridParams($params);

        try {
            foreach ($users as $user) {
                $this->ensureUserHasNoRelatedEntities($user);
                $this->get('diamante.user.service')->removeDiamanteUser($user->getId());
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
    public function resetPasswordAction(Request $request, $id)
    {
        $user = new User($id, User::TYPE_DIAMANTE);

        try {
            $this->get('diamante.user.service')->resetPassword($user);
            return new Response(null, 204, array(
                'Content-Type' => $request->getMimeType('json')
            ));
        } catch (\Exception $e) {
            $this->handleException($e);
            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @Route("/reset/massaction", name="diamante_user_reset_pwd_massaction", options={"expose" = true})
     */
    public function massResetPasswordAction(Request $request)
    {
        $users = $request->get('values');

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

    protected function ensureUserHasNoRelatedEntities($userId)
    {
        $entityMap = [
            'tickets' => ['DiamanteDeskBundle:Ticket', 'reporter']
        ];

        $user = new User($userId, User::TYPE_DIAMANTE);

        foreach ($entityMap as $type => $entityConfig) {
            list($mapping, $field) = $entityConfig;

            $repo = $this->get('doctrine')->getRepository($mapping);
            $entities = $repo->findBy([$field => $user]);

            if (!empty($entities)) {
                throw new UserRemovalException(sprintf("User has related %s, can not delete user", $type));
            }
        }
    }

    /**
     * @return array
     */
    protected function parseGridParameters(Request $request)
    {
        $parametersParser = $this->container->get('oro_datagrid.mass_action.parameters_parser');
        return $parametersParser->parse($request);
    }
}
