<?php

namespace Sway\Component\Dependency;

use Sway\Component\Dependency\Exception;

class DependencyInterface
{
    /**
     * Dependencies's container
     * @var \Sway\Component\Dependency\DependencyContainer
     */
    protected $dependencyContainer = null;
    
  
    /**
     * Stores dependency container
     * @param \Sway\Component\Dependency\DependencyContainer $dependencyContainer
     * @throws \Sway\Component\Dependency\Exception\DependencyContainerException
     */
    public function setDependencyContainer(DependencyContainer $dependencyContainer)
    {   
        /**
         * Stores dependency container if dependency container has not been defined earlier
         */
        if (empty($this->dependencyContainer)){
            if (empty($dependencyContainer)){
                throw Exception\DependencyContainerException::emptyDependencyContainerException();
            }
            else{             
                $this->dependencyContainer = $dependencyContainer;
                $this->dependencyController();              
            }
        }
    }
    
    /**
     * This method is helpful if you want stores dependencies into your class's fields
     */
    protected function dependencyController()
    {
        
    }
    
    /**
     * Gets dependency
     * @param string $dependencyName
     * @return object
     */
    public function getDependency(string $dependencyName)
    {
        return $this->dependencyContainer->getDependency($dependencyName);
    }
}


?>