<?php

namespace Sway\Component\Dependency\Exception;


class DependencyException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Throws an exception when dependency is not defined
     * @param string $dependencyName
     * @return \Sway\Component\Dependency\Exception\DependencyException
     */
    public static function dependencyMissedException(string $dependencyName) : DependencyException
    {
        $dependencyException = new DependencyException(sprintf("Dependency '%s' is not defined", $dependencyName));
        return $dependencyException;
    }
    
    /**
     * Throws an exception when dependency object is empty (is null)
     * @param string $dependencyName
     * @return \Sway\Component\Dependency\Exception\DependencyException
     */
    public static function emptyDependencyException(string $dependencyName) : DependencyException
    {
        return (new DependencyException(sprintf("Dependency '%s' is empty", $dependencyName)));
    }
}


?>
