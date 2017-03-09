<?php

namespace Sway\Component\Init;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Autoloader;

class InitAutoloader extends DependencyInterface
{
    
    public function __construct() 
    {
        
    }
    
    public function init()
    {
        $autoloaderContainer = Autoloader\AutoloaderContainer::create();
        $this->getDependency('injector')->inject($autoloaderContainer);
        $this->getDependency('injector')->createDependency('autoloader', $autoloaderContainer);
        return $autoloaderContainer;
        
    }
    
}


?>