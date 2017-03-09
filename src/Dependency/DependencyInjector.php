<?php

namespace Sway\Component\Dependency;

use Sway\Component\Dependency\Exception;

class DependencyInjector
{
    /**
     * Dependency container
     * @var \Sway\Component\Dependency\DependencyContainer
     */
    private $dependencyContainer = null;
    
    /**
     * Creates an dependency injector
     * @param \Sway\Component\Dependency\DependencyContainer $dependencyContainer
     */
    public function __construct(DependencyContainer $dependencyContainer = null) 
    {
        /**
         * If custom dependency contaiener is passed
         */
        if (!empty($dependencyContainer)){
            $this->dependencyContainer = $dependencyContainer;
        }
        /**
         * Else, create empty dependency container
         */
        else{
            $this->dependencyContainer = new DependencyContainer();
        }
    }
    
    /**
     * Inject dependency container to object if possible
     * @param object $object
     */
    public function inject($object)
    {
        $classReflector = new \ReflectionClass($object);
        
        /**
         * If object implements DependencyInterface
         */
        if ($classReflector->hasMethod('setDependencyContainer')){
            $object->setDependencyContainer($this->dependencyContainer);
        }
    }
    
    /**
     * Creates a dependency
     * @param string $dependencyName
     * @param object $dependencyObject
     */
    public function createDependency(string $dependencyName, $dependencyObject)
    {
        $this->dependencyContainer->addDependency($dependencyName, $dependencyObject);
    }
    
    
}


?>

