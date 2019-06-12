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

use Diamante\DeskBundle\Model\Branch\Exception\DuplicateBranchKeyException;
use Diamante\DeskBundle\Api\Command\BranchCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
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
    public function createAction()
    {
        $command = new BranchCommand();
        try {
            $form = $this->createForm('diamante_branch_form', $command);

            $result = $this->edit($command, $form, function($command) {
                $branch = $this->get('diamante.branch.service')->createBranch($command);
                return $branch->getId();
            });
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
     * @return array
     */
    public function updateAction($id)
    {
        $branch = $this->get('diamante.branch.service')->getBranch($id);
        $command = BranchCommand::fromBranch($branch);

        try {
            $form = $this->createForm('diamante_update_branch_form', $command);

            $result = $this->edit($command, $form, function($command) use ($branch) {
                $branchId = $this->get('diamante.branch.service')->updateBranch($command);
                return $branchId;
            });
        } catch (MethodNotAllowedException $e) {
            return $this->redirect(
                $this->generateUrl(
                    'diamante_branch_view',
                    array(
                        'id' => $id
                    )
                )
            );
        } catch (\Exception $e) {
            $this->handleException($e);
            return $this->redirect(
                $this->generateUrl(
                    'diamante_branch_view',
                    array(
                        'id' => $id
                    )
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
    private function edit(BranchCommand $command, $form, $callback)
    {
        $response = null;
        try {
            $this->handle($form);
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
            if (is_null($command->key) || empty($command->key)) {
                $formView->children['key']->vars = array_replace(
                    $formView->children['key']->vars,
                    array('value' => $this->get('diamante.branch.default_key_generator')->generate($command->name))
                );
            }
            $response = array('form' => $formView);
        } catch (\Exception $e) {
            $this->handleException($e);
            $response = array('form' => $form->createView());
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
    public function deleteAction($id)
    {
        try {
            $this->get('diamante.branch.service')
                ->deleteBranch($id);
            if (false === $this->isDeletionRequestFromGrid()) {
                $this->addSuccessMessage('diamante.desk.branch.messages.delete.success');
            }
            return new Response(null, 204, array(
                'Content-Type' => $this->getRequest()->getMimeType('json')
            ));
        } catch (\Exception $e) {
            $this->handleException($e);
            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @return bool
     */
    private function isDeletionRequestFromGrid()
    {
        $referer = $this->getRequest()->headers->get('referer');

        if (empty($referer)) {
            return false;
        }

        $parts = explode('/', $referer);
        $origin = array_pop($parts);

        return 'branches' === $origin;
    }
}
