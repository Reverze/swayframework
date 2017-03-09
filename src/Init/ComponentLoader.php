<?php

namespace Sway\Component\Init;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Dependency\Exception\DependencyException;

class ComponentLoader extends DependencyInterface
{
    /**
     * Array which contains component's parameters
     * @var array
     */
    private $components = null;
    
    public function __construct()
    {
        if (empty($this->components)){
            $this->components = array();
        }
    }
    
    /**
     * After dependency injection, initializes components
     */
    protected function dependencyController() 
    {
        $this->initializeComponents();
    }

    /**
     * Creates uninitialized componentLoader
     * @return \Sway\Component\Init\ComponentLoader
     */
    public static function create() : ComponentLoader
    {
        $componentLoader = new ComponentLoader();
        return $componentLoader;
    }
    
    /**
     * Initialize components
     */
    protected function initializeComponents()
    {
        $this->components = $this->getDependency('framework')->getCfg('framework/component.init');
        
        
        
        foreach ($this->components as $componentName => $componentParameters){
            /**
             * If components is not loaded, method getDependency throws an exception
             */
            try{
                $this->getDependency($componentName);
            } 
            catch (DependencyException $ex) {
                $this->loadComponent($componentName);
            }
            
          
        }  
        
    }
    
    /**
     * Loads component as dependency
     * @param string $componentName
     * @throws \Sway\Component\Dependency\Exception\DependencyExceptio
     */
    protected function loadComponent(string $componentName)
    {
        $componentParameters = $this->components[$componentName];
        
        /**
         * Component's controller
         */
        $componentController = null;
        
        /**
         * Component's controller must be defined
         */
        if (!array_key_exists('controller', $componentParameters)){
            throw Exception\ComponentLoaderException::componentControllerNotDefined($componentName);
        }
        else{
            $componentController = (string) $componentParameters['controller'];
        }
        
        /**
         * When controller is not exists
         */
        if (!class_exists($componentController)){
            throw Exception\ComponentLoaderException::componentControllerNotExists($componentName);
        }
        
        $requiredComponents = array();
        
        if (array_key_exists('require', $componentParameters)){
            $requiredComponents = $componentParameters['require'];
        }
       
        /**
         * Loads required components
         */
        foreach ($requiredComponents as $requiredComponent){
            try{
                $this->getDependency($requiredComponent);
            } 
            catch (DependencyException $ex) {
                $this->loadComponent($requiredComponent);
            }
        }
        /**
         * Creates a new instance of component init
         */
        $componentInit = new $componentController($componentName);
        
        $this->getDependency('injector')->inject($componentInit);
        /**
         * Method object should returns component's object
         */
        $componentObject = $componentInit->init();
        
        $this->getDependency('injector')->createDependency($componentName, $componentObject); 
        if (!$componentInit->isInjected()){
            $this->getDependency('injector')->inject($componentObject);
        }
        
        $componentInit->after();
               
        
    }   
    
}


?>