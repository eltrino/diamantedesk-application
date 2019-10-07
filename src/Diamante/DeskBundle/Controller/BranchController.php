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
namespace Diamante\DeskBundle\Controller;

use Diamante\DeskBundle\Form\Type\CreateBranchType;
use Diamante\DeskBundle\Form\Type\UpdateBranchType;
use Diamante\DeskBundle\Model\Branch\Exception\DuplicateBranchKeyException;
use Diamante\DeskBundle\Api\Command\BranchCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * @Route("branches")
 */
class BranchController extends Controller
{
    use Shared\FormHandlerTrait;
    use Shared\ExceptionHandlerTrait;
    use Shared\SessionFlashMessengerTrait;
    use Shared\ResponseHandlerTrait;
    use Shared\RequestGetterTrait;

    /**
     * @Route(
     *      "/{_format}",
     *      name="diamante_branch_list",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     */
    public function listAction()
    {
        return [];
    }

    /**
     * @Route(
     *      "/view/{id}",
     *      name="diamante_branch_view",
     *      requirements={"id"="\d+"}
     * )
     * @Template
     *
     * @param string $id
     *
     * @return array|Response
     */
    public function viewAction($id)
    {
        try {
            $branch = $this->get('diamante.branch.service')->getBranch($id);

            return ['entity' => $branch];
        } catch (\Exception $e) {
            $this->handleException($e);
            throw $this->createNotFoundException($e->getMessage(), $e);
        }
    }

    /**
     * @Route("/create", name="diamante_branch_create")
     * @Template("DiamanteDeskBundle:Branch:create.html.twig")
     */
    public function createAction(Request $request)
    {
        $command = new BranchCommand();
        try {
            $form = $this->createForm(CreateBranchType::class, $command);

            $result = $this->edit(
                $request,
                $command,
                $form,
                function ($command) {
                    $branch = $this->get('diamante.branch.service')->createBranch($command);

                    return $branch->getId();
                }
            );
        } catch (\Exception $e) {
            $this->handleException($e);

            return $this->redirect(
                $this->generateUrl(
                    'diamante_branch_create'
                )
            );
        }

        return $result;
    }

    /**
     * @Route(
     *      "/update/{id}",
     *      name="diamante_branch_update",
     *      requirements={"id"="\d+"}
     * )
     * @Template("DiamanteDeskBundle:Branch:update.html.twig")
     *
     * @param int $id
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $id)
    {
        $branch = $this->get('diamante.branch.service')->getBranch($id);
        $command = BranchCommand::fromBranch($branch);

        try {
            $form = $this->createForm(UpdateBranchType::class, $command);

            $result = $this->edit(
                $request,
                $command,
                $form,
                function ($command) {
                    return $this->get('diamante.branch.service')->updateBranch($command);
                }
            );
        } catch (MethodNotAllowedException $e) {
            return $this->redirect(
                $this->generateUrl(
                    'diamante_branch_view',
                    [
                        'id' => $id,
                    ]
                )
            );
        } catch (\Exception $e) {
            $this->handleException($e);

            return $this->redirect(
                $this->generateUrl(
                    'diamante_branch_view',
                    [
                        'id' => $id,
                    ]
                )
            );
        }

        return $result;
    }

    /**
     * @param BranchCommand $command
     * @param \Closure $callback
     * @param Form $form
     * @return array
     */
    private function edit(Request $request, BranchCommand $command, $form, $callback)
    {
        $response = null;
        try {
            $this->handle($request, $form);
            if ($command->defaultAssignee) {
                $command->defaultAssignee = $command->defaultAssignee->getId();
            }
            $branchId = $callback($command);
            if ($command->id) {
                $this->addSuccessMessage('diamante.desk.branch.messages.save.success');
            } else {
                $this->addSuccessMessage('diamante.desk.branch.messages.create.success');
            }
            $response = $this->getSuccessSaveResponse(
                'diamante_branch_update',
                'diamante_branch_view',
                ['id' => $branchId]
            );
        } catch (DuplicateBranchKeyException $e) {
            $this->addErrorMessage($e->getMessage());
            $formView = $form->createView();
            if ($command->key === null || empty($command->key)) {
                $formView->children['key']->vars = array_replace(
                    $formView->children['key']->vars,
                    ['value' => $this->get('diamante.branch.default_key_generator')->generate($command->name)]
                );
            }
            $response = ['form' => $formView];
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = ['form' => $form->createView()];
        }

        return $response;
    }

    /**
     * @Route(
     *      "/delete/{id}",
     *      name="diamante_branch_delete",
     *      requirements={"id"="\d+"}
     * )
     *
     * @param int $id
     * @return Response
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $this->get('diamante.branch.service')
                ->deleteBranch($id);
            if (false === $this->isDeletionRequestFromGrid($request)) {
                $this->addSuccessMessage('diamante.desk.branch.messages.delete.success');
            }

            return new Response(
                null, 204, [
                'Content-Type' => $request->getMimeType('json'),
            ]
            );
        } catch (\Exception $e) {
            $this->handleException($e);

            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isDeletionRequestFromGrid(Request $request): bool
    {
        $referer = $request->headers->get('referer');

        if (empty($referer)) {
            return false;
        }

        $parts = explode('/', $referer);
        $origin = array_pop($parts);

        return 'branches' === $origin;
    }
}
