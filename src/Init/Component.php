<?php

namespace Sway\Component\Init;

use Sway\Component\Dependency\DependencyInterface;

class Component extends DependencyInterface
{
    /**
     * 
     * @var bool
     */
    private $injected = false;
    public function __construct(string $componentName)
    {
        
        
    }
    
    /**
     * 
     */
    protected function setAsInjected()
    {
        $this->injected = true;
    }
    
    public function isInjected()
    {
        return $this->injected;
    }


    public function init()
    {
        
    }
    
    /**
     * Callback after init component
     */
    public function after()
    {
        
    }
    
}

?>
