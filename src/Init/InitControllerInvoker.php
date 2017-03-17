<?php

namespace Sway\Component\Init;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Controller\Invoker;

class InitControllerInvoker extends DependencyInterface
{
    
    public function __construct() 
    {
        
    }
    
    /**
     * Inits controllerInvoker as dependency
     */
    public function init()
    {
        $controllerInvoker = Invoker::create();
        $this->getDependency('injector')->inject($controllerInvoker);
        $this->getDependency('injector')->createDependency('controllerInvoker', $controllerInvoker);
    }
    
}


?>

