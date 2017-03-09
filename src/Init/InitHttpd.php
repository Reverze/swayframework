<?php

namespace Sway\Component\Init;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Httpd\HttpdInterface;

class InitHttpd extends DependencyInterface
{ 
    public function __construct()
    {
        
    }
    
    public function init()
    {
        $httpdInterface = HttpdInterface::createFromGlobals();
        $this->getDependency('injector')->inject($httpdInterface);
        $this->getDependency('injector')->createDependency('httpd', $httpdInterface);     
    }  
}


?>

