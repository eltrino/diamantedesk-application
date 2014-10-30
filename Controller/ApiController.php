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

use SebastianBergmann\Exporter\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @Route("desk")
 *
 * @todo refactor this to avoid all logic in one method, different response code for different requests methods, cover with tests
 * @todo build map depending on services available in system
 * @todo content type from headers
 * @todo input data depending on headers (json, xml ad currently only POST data supported param1=val1&param2=val2&...)
 * @todo generate routes
 * @todo apidoc
 */
class ApiController extends Controller
{
    private $routeParams;

    /**
     * @Route(
     *      "{resource}.{format}",
     *      name="diamante_desk_api_bundle",
     *      requirements={"format"="json|xml", "resource"="[^.]+"},
     *      defaults={"format"="json"}
     * )
     */
    public function processAction($resource, $format)
    {
        $method = $this->getRequest()->getMethod();
        $serializer = $this->container->get('jms_serializer');

        $service = $this->container->get('diamante.branch.service');
        $class = get_class($service);

        $reflection = new \ReflectionClass($class);
        foreach ($reflection->getMethods() as $reflectionMethod) {
            if (preg_match(
                '/@api\s\{(get|post|put|delete)\}\s([^\s]+)\s.*/',
                $reflectionMethod->getDocComment(),
                $matches
            )
            ) {
                $this->routeParams = [];
                $matchMethod = $matches[1];
                $matchResource = $this->regexp($matches[2]);
                if (strtolower($matchMethod) == strtolower($method) && preg_match(
                        $matchResource,
                        $resource,
                        $params
                    )
                ) {
                    $reflectionParameters = $reflectionMethod->getParameters();

                    foreach ($params as $key => $value) {
                        if (!in_array($key, $this->routeParams, true)) {
                            unset($params[$key]);
                        }
                    }

                    if (count($params) > 0) {
                        // @todo avoid hard coding this
                        $params['properties'] = $this->getRequest()->request->all();
                    } else {
                        $params = $this->getRequest()->request->all();
                    }

                    try {
                        if (empty($reflectionParameters)) {
                            $args = [];
                        } elseif (isset($reflectionParameters[0])) {
                            $param = $reflectionParameters[0];

                            if (is_null($param->getClass())) {
                                $args = $params;
                            } else {
                                /** @var \Symfony\Component\Validator\Validator $validator */
                                $validator = $this->container->get('validator');
                                $commandClassName = $param->getClass()->name;

                                $command = new $commandClassName;
                                $commandProperties = get_class_vars($commandClassName);
                                foreach ($params as $name => $value) {
                                    if (array_key_exists($name, $commandProperties)) {
                                        if (is_numeric($value)) {
                                            $value = $value * 1;
                                        }
                                        $command->$name = $value;
                                    }
                                }
                                $errors = $validator->validate($command);

                                if (count($errors) > 0) {
                                    $errorsString = (string)$errors;
                                    throw new \InvalidArgumentException($errorsString);
                                }
                                $args = [$command];
                            }
                        } else {
                            throw new \LogicException();
                        }

                        $result = call_user_func_array(array($service, $reflectionMethod->getName()), $args);

                        if (!$result) {
                            return new Response(null, 200);
                        }
                        return new Response($serializer->serialize($result, $format), 200);
                    } catch (Exception $e) {
                        return new Response($serializer->serialize(array('error' => $e->getMessage()), $format), 400);
                    }
                }
            }
        }

        return new Response($serializer->serialize(array('error' => 'Not Implemented'), $format), 501);
    }

    private function regexp($pattern)
    {
        $expression = preg_replace_callback(
            '#{([\w]+)}#',
            array($this, 'registerParam'),
            $pattern
        );

        return "#^" . $expression . "$#";
    }

    private function registerParam($m)
    {
        $this->routeParams[] = $m[1];
        return '(?<' . $m[1] . '>[^/]+)';
    }
}
