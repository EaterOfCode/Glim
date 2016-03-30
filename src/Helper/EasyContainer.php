<?php
/**
 * Created by PhpStorm.
 * User: eater
 * Date: 3/30/16
 * Time: 2:04 AM
 */

namespace Eater\Glim\Helper;


use Slim\Container;

trait EasyContainer
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
    
    /**
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        if (is_callable($value)) {
            $value = $this->getContainer()->protect($value);
        }
        
        $this->getContainer()->offsetSet($name, $value);
    }
    
    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->getContainer()->get($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->getContainer()->has($name);
    }

}