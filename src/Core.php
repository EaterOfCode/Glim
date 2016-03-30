<?php

namespace Eater\Glim;


use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\Config;
use Propel\Runtime\Propel;
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Yaml\Yaml;
use Eater\Glim\Helper\EasyContainer;

class Core implements ContainerInterface
{
    use EasyContainer;

    /**
     * @var string
     */
    private $baseDir;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var array
     */
    private $timers = [];
    /**
     * @var array
     */
    private $routes;

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->get('twig');
    }

    /**
     * @return Propel
     */
    public function getPropel()
    {
        return $this->get('propel');
    }

    /**
     * @param Propel $propel
     */
    public function setPropel($propel)
    {
        $this->set('propel', $propel);
    }

    public function boot($baseDir)
    {
        $this->startTimer(["boot", "load"]);

        $this->setBaseDir($baseDir);
        $this->loadConfig();
        $this->bootContainer();
        $this->bootLogger();
        $this->loadRoutes();
        $this->loadContainer();
        $this->loadServices();
        $this->loadPropel();
        $this->loadSlim();
        $this->loadTwig();

        $this->endTimer(["load"]);
        $this->startTimer(["process"]);

        $this->processRoutes();

        $this->endTimer(["boot", "process"]);
    }

    public function startTimer($timers)
    {
        $time = microtime(true);
        foreach ($timers as $timer) {
            $this->timers[$timer] = [$time, false];
        }
    }

    public function loadConfig()
    {
        $basePath = '?' . $this->getBaseDir() . '/config/app.';
        $configs = ['yml', 'json', 'xml', 'ini'];

        $configPaths = array_map(function ($ext) use ($basePath) {
            return $basePath . $ext;
        }, $configs);

        $this->setConfig(new Config($configPaths));
    }

    /**
     * @return string
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * @param string $baseDir
     */
    public function setBaseDir($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function bootContainer()
    {
        $this->setContainer(new Container());
    }

    public function bootLogger()
    {
        
        $config = $this->getConfig();

        $logger = new Logger('default');
        $logger->pushHandler(
            new StreamHandler(
                $config->get('core.log.path', $this->getBaseDir() . '/logs/core.log'),
                $config->get('core.log.level', Logger::INFO)
            )
        );

        $this->setLogger($logger);
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->set('logger', $logger);
    }

    public function loadRoutes()
    {
        $this->startTimer(["load/routes"]);

        $path = $this->getBaseDir() . '/config/routes.yml';
        $routesFile = file_get_contents($path);
        $routes = Yaml::parse($routesFile);
        $this->setRoutes($routes);

        $this->endTimer(["load/routes"]);
    }

    public function endTimer($timers)
    {
        $logger = $this->getLogger();
        $time = microtime(true);
        foreach ($timers as $timer) {
            $this->timers[$timer][1] = $time;

            $elapsed = round(($this->timers[$timer][1] - $this->timers[$timer][0]) * 1000);

            $logger->addDebug("[CORE] {$timer} took {$elapsed}ms");
        }
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->get('logger');
    }

    public function loadContainer()
    {
        $this->startTimer(["load/container"]);

        $container = $this->getContainer();
        $containerSettings = $this->getConfig()->get('core.container', []);

        $container->offsetSet("baseDir", $this->getBaseDir());

        foreach ($containerSettings as $name => $value) {
            $container->offsetSet($name, $value);
        }

        $this->endTimer(["load/container"]);
    }

    public function loadServices()
    {
        $this->startTimer(['load/services']);

        $services = $this->getConfig()->get('core.services', []);
        $container = $this->getContainer();

        foreach ($services as $name => $class) {
            $container->offsetSet($name, function ($container) use ($class, $name) {
                return $class::init($container, $name);
            });
        }

        $this->endTimer(['load/services']);
    }

    public function loadPropel()
    {
        $this->startTimer(["load/propel"]);

        Propel::getServiceContainer()->setLogger('defaultLogger', $this->getLogger());

        $this->endTimer(["load/propel"]);
    }

    public function loadSlim()
    {
        $this->startTimer(["load/slim"]);

        $slim = new App($this->getContainer());

        $this->setSlim($slim);

        $this->endTimer(["load/slim"]);
    }

    /**
     * @param App $slim
     */
    public function setSlim($slim)
    {
        $this->set('slim', $slim);
    }

    public function loadTwig()
    {
        $this->startTimer(["load/twig"]);

        $loader = new \Twig_Loader_Filesystem($this->getBaseDir() . '/views/');
        $twig = new \Twig_Environment($loader, array(
            'cache' => $this->getBaseDir() . '/tmp/twig',
            'debug' => $this->getConfig()->get('core.debug', false)
        ));

        $this->setTwig($twig);

        $this->endTimer(["load/twig"]);
    }

    /**
     * @param \Twig_Environment $twig
     */
    public function setTwig($twig)
    {
        $this->set('twig', $twig);
    }

    public function processRoutes()
    {
        $this->startTimer(["process/routes"]);

        $routesConfig = $this->getRoutes();
        $prefix = $routesConfig["prefix"];
        $routes = $routesConfig["routes"];

        $this->processRoute("", $routes, $prefix);

        $this->endTimer(["process/routes"]);
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param string $path
     * @param array $routes
     * @param string $prefix
     */
    public function processRoute($path, $routes, $prefix)
    {
        $slim = $this->getSlim();
        $core = $this;


        $handler = function ($fullRoute) use ($core) {
            return function (Request $request, Response $response) use ($fullRoute, $core) {
                return $core->handle($fullRoute, $request, $response, $this);
            };
        };

        foreach ($routes as $child => $route) {
            if ($child[0] !== '/') {
                $slim->map([strtoupper($child)], $path, $handler($prefix . $route));
                continue;
            }

            $fullPath = $path . $child;

            if (is_string($route)) {
                $slim->get($fullPath, $handler($prefix . $route));

                continue;
            } else {
                $this->processRoute($fullPath, $route, $prefix);
            }

        }
    }

    /**
     * @return App
     */
    public function getSlim()
    {
        return $this->get('slim');
    }

    /**
     * @param string $class
     * @param Request $request
     * @param Response $response
     * @param ContainerInterface $containerInterface
     * @return Response
     */
    public function handle($class, Request $request, Response $response, ContainerInterface $containerInterface)
    {
        $this->startTimer(['response']);
        $handler = new $class($this, $request, $response, $containerInterface);
        $response = $handler->handle();
        $this->endTimer(['response']);

        return $response;
    }

    public function run()
    {
        $this->getSlim()->run();
    }

}