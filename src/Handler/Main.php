<?php
/**
 * Created by PhpStorm.
 * User: eater
 * Date: 3/29/16
 * Time: 10:21 PM
 */

namespace Eater\Glim\Handler;


use Interop\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Eater\Glim\Core;
use Eater\Glim\Helper\EasyContainer;

abstract class Main implements ContainerInterface
{
    use EasyContainer;
    
    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Main constructor.
     * @param Core $core
     * @param Request $request
     * @param Response $response
     * @param Container $container
     */
    public function __construct(Core $core, Request $request, Response $response, Container $container)
    {
        $this->core = $core;
        $this->setRequest($request);
        $this->setResponse($response);
        $this->setContainer($container);
    }

    /**
     * @return Core
     */
    protected function getCore()
    {
        return $this->core;
    }

    public function handle()
    {
        return $this->getResponse();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function render($template, $context = [])
    {
        $core = $this->getCore();
        $twig = $core->getTwig();

        return $this->getResponse()->write($twig->render($template, $context));
    }
}