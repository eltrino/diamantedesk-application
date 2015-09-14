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
 
namespace Diamante\DistributionBundle\EventListener;

use Diamante\DistributionBundle\Routing\Whitelist\WhitelistProvider;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use  Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;

class OroApiCallRestrictionListener
{
    const VIEW_FORMAT = 'html';

    /**
     * @var WhitelistProvider
     */
    protected $provider;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var ViewHandler
     */
    protected $viewHandler;

    /**
     * @var \AppKernel
     */
    protected $kernel;

    /**
     * @param WhitelistProvider $provider
     * @param Logger            $logger
     * @param ViewHandler       $viewHandler
     * @param \AppKernel        $kernel
     * @param DelegatingEngine  $templating
     */
    public function __construct(
        WhitelistProvider $provider,
        Logger $logger,
        ViewHandler $viewHandler,
        \AppKernel $kernel,
        DelegatingEngine $templating
    ) {
        $this->provider      = $provider;
        $this->logger        = $logger;
        $this->viewHandler   = $viewHandler;
        $this->kernel        = $kernel;
        $this->templating    = $templating;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $route = $request->attributes->get('_route');
        if (empty($route)) {
            $route = $request->attributes->get('_master_request_route');
        }

        if (!$this->provider->isItemWhitelisted($route)){
            $notFoundException = new NotFoundHttpException('Sorry, the page that you requested was not found.');
            $statusCode = $notFoundException->getStatusCode();
            $parameters = [
                'status_code'    => $statusCode,
                'status_text'    => Response::$statusTexts[$statusCode],
                'currentContent' => '',
                'exception'      => FlattenException::create($notFoundException),
                'logger'         => $this->logger
            ];

            $view = View::create($parameters);
            $view->setFormat(self::VIEW_FORMAT);
            $view->setTemplate($this->findTemplate($request, $statusCode, $this->kernel->isDebug()));
            $response = $this->viewHandler->handle($view);
            $event->setResponse($response);
        }
    }

    /**
     * @param Request $request
     * @param         $statusCode
     * @param         $debug
     *
     * @return TemplateReference
     */
    protected function findTemplate(Request $request, $statusCode, $debug)
    {
        $name = $debug ? 'exception' : 'error';
        if ($debug) {
            $name = 'exception_full';
        }

        // when not in debug, try to find a template for the specific HTTP status code and format
        if (!$debug) {
            $template = new TemplateReference('TwigBundle', 'Exception', $name.$statusCode, self::VIEW_FORMAT, 'twig');
            if ($this->templating->exists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = new TemplateReference('TwigBundle', 'Exception', $name, self::VIEW_FORMAT, 'twig');
        if ($this->templating->exists($template)) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat(self::VIEW_FORMAT);

        return new TemplateReference('TwigBundle', 'Exception', $name, self::VIEW_FORMAT, 'twig');
    }
}