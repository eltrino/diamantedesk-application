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

use Diamante\DeskBundle\Model\Branch\DuplicateBranchKeyException;

use Diamante\DeskBundle\Api\Command\BranchCommand;
use Diamante\DeskBundle\Api\Command\BranchEmailConfigurationCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Diamante\DeskBundle\Form\Type\CreateBranchType;
use Diamante\DeskBundle\Form\Type\UpdateBranchType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @Route("branches")
 */
class BranchController extends Controller
{
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
            $branchEmailConfiguration = $this->get('diamante.branch_email_configuration.service')
                ->getConfigurationByBranchId($branch->getId());
            return [
                'entity' => $branch,
                'branchEmailConfiguration' => $branchEmailConfiguration
            ];
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(sprintf('Branch loading failed: %s', $e->getMessage()));
            return new Response($e->getMessage(), 404);
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
            $form = $this->createForm(new CreateBranchType(), $command);

            $result = $this->edit($command, $form, function ($command) {
                $branch = $this->get('diamante.branch.service')->createBranch($command);
                $this->createBranchEmailConfiguration($command, $branch->getId());
                return $branch->getId();
            });
        } catch (MethodNotAllowedException $e) {
        } catch(\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(sprintf('Branch creation failed: %s', $e->getMessage()));
            $this->addErrorMessage('diamante.desk.branch.messages.create.error');
            return $this->redirect(
                $this->generateUrl(
                    'diamante_branch_create'
                )
            );
        }
        return $result;
    }

    /**
     * @param $command
     * @param $branchId
     */
    private function createBranchEmailConfiguration(BranchCommand $command, $branchId)
    {
        $branchEmailConfigurationCommand = $command->getBranchEmailConfiguration();
        $branchEmailConfigurationCommand->branch = $branchId;
        $this->get('diamante.branch_email_configuration.service')->createBranchEmailConfiguration($branchEmailConfigurationCommand);
    }

    /**
     * @param $command
     * @param $branchId
     */
    private function updateBranchEmailConfiguration(BranchCommand $command, $branchId)
    {
        $branchEmailConfigurationCommand = $command->getBranchEmailConfiguration();
        $branchEmailConfigurationCommand->branch = $branchId;
        $this->get('diamante.branch_email_configuration.service')->updateBranchEmailConfiguration($branchEmailConfigurationCommand);
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

        if ($this->get('diamante.branch_email_configuration.service')->getConfigurationByBranchId($id)) {
            $branchEmailConfiguration = $this->get('diamante.branch_email_configuration.service')->getConfigurationByBranchId($id);
            $branchEmailConfigurationCommand = BranchEmailConfigurationCommand::fromBranchEmailConfiguration($branchEmailConfiguration);
            $command->setBranchEmailConfiguration($branchEmailConfigurationCommand);
        }

        try {
            $form = $this->createForm(new UpdateBranchType(), $command);

            $result = $this->edit($command, $form, function ($command) use ($branch) {
                $branchId = $this->get('diamante.branch.service')->updateBranch($command);
                $this->updateBranchEmailConfiguration($command, $branchId);
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
        } catch(\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(sprintf('Branch update failed: %s', $e->getMessage()));
            $this->addErrorMessage('diamante.desk.branch.messages.save.error');
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
     * @param $callback
     * @param UpdateBranchType $form
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
            $response = $this->getSuccessSaveResponse($branchId);
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
        } catch (MethodNotAllowedException $e) {
            $response = array('form' => $form->createView());
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(sprintf('Branch saving failed: %s', $e->getMessage()));
            $this->addErrorMessage('diamante.desk.branch.messages.save.error');
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
            if (false == $this->isDeletionRequestFromGrid()) {
                $this->addSuccessMessage('diamante.desk.branch.messages.delete.success');
            }
            return new Response(null, 204, array(
                'Content-Type' => $this->getRequest()->getMimeType('json')
            ));
        } catch (MethodNotAllowedException $e) {
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.diamante')->error(sprintf('Branch deletion failed: %s', $e->getMessage()));
            $this->addErrorMessage('diamante.desk.branch.messages.delete.error');
            return new Response($e->getMessage(), 500);
        }
    }

    /**
     * @param Form $form
     * @throws MethodNotAllowedException
     * @throws ValidatorException
     */
    private function handle(Form $form)
    {
        if (false === $this->getRequest()->isMethod('POST')) {
            throw new MethodNotAllowedException(array('POST'), 'Form can be posted only by "POST" method.');
        }

        $form->handleRequest($this->getRequest());

        if (false === $form->isValid()) {
            throw new ValidatorException('Form object validation failed, form is invalid.');
        }
    }

    /**
     * @param $message
     */
    private function addSuccessMessage($message)
    {
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans($message)
        );
    }

    /**
     * @param $message
     */
    private function addErrorMessage($message)
    {
        $this->get('session')->getFlashBag()->add(
            'error',
            $this->get('translator')->trans($message)
        );
    }

    /**
     * @param int $branchId
     * @return mixed
     */
    private function getSuccessSaveResponse($branchId)
    {
        return $this->get('oro_ui.router')->redirectAfterSave(
            ['route' => 'diamante_branch_update', 'parameters' => ['id' => $branchId]],
            ['route' => 'diamante_branch_view', 'parameters' => ['id' => $branchId]]
        );
    }

    private function isDeletionRequestFromGrid()
    {
        $referer = $this->getRequest()->headers->get('referer');

        if (empty($referer)) {
            return false;
        }

        $origin = array_pop(explode('/', $referer));

        return 'branches' === $origin;
    }
}
