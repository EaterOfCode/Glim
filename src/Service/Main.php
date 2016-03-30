<?php
/**
 * Created by PhpStorm.
 * User: eater
 * Date: 3/30/16
 * Time: 9:09 PM
 */

namespace Eater\Glim\Service;


use Eater\Glim\Helper\EasyContainer;
use Slim\Container;

abstract class Main
{
    use EasyContainer;

    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Main constructor.
     * @param Container $container
     * @param string $name
     */
    public function __construct($container, $name)
    {
        $this->setContainer($container);
        $this->setName($name);
    }

    /**
     * @param Container $container
     * @param string $name
     * @return static
     */
    static public function init($container, $name)
    {
        return new static($container, $name);
    }
}