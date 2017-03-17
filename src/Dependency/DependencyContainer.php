<?php

namespace Sway\Component\Dependency;

use Sway\Component\Dependency\Exception;

class DependencyContainer
{
    /**
     * Arrays which contains dependecies object
     * @var array
     */
    private $dependencies = array();
    
    public function __construct()
    {
        
    }
    
    /**
     * Stores dependency into container
     * @param string $dependencyName
     * @param type $dependency
     * @throws type
     */
    public function addDependency(string $dependencyName, $dependency)
    {
        if (empty($dependency)){
            throw Exception\DependencyException::emptyDependencyException($dependencyName);
        }
        $this->dependencies[$dependencyName] = $dependency;
    }
    
    /**
     * Gets dependency from container
     * @param string $dependencyName
     * @return type
     * @throws \Sway\Component\Dependency\Exception\DependencyException
     */
    public function getDependency(string $dependencyName)
    {
        if (isset($this->dependencies[$dependencyName])){
            return $this->dependencies[$dependencyName];
        }
        else{
            throw Exception\DependencyException::dependencyMissedException($dependencyName);
        }
    }
    
}


?>