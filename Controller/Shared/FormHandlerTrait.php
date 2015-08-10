<?php

namespace Diamante\DeskBundle\Controller\Shared;


use Symfony\Component\Form\Form;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Exception\ValidatorException;

trait FormHandlerTrait
{
    /**
     * @param Form $form
     * @throws MethodNotAllowedException
     * @throws ValidatorException
     */
    protected function handle(Form $form)
    {
        if (false === $this->getRequest()->isMethod('POST')) {
            throw new MethodNotAllowedException(array('POST'),'Form can be posted only by "POST" method.');
        }

        $form->handleRequest($this->getRequest());

        if (false === $form->isValid()) {
            throw new ValidatorException('Form object validation failed, form is invalid.');
        }
    }

    abstract public function getRequest();
}