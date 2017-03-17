<?php

namespace Sway\Component\Init\Exception;

class ComponentLoaderException extends \Exception
{
    /**
     * Throws an exception when component's controller is not defined
     * @param string $componentName
     * @return \Sway\Component\Init\Exception\ComponentLoaderException
     */
    public static function componentControllerNotDefined(string $componentName) : ComponentLoaderException
    {
        return (new ComponentLoaderException(sprintf("Controller is not specified for '%s'", $componentName)));
    }
    
    /**
     * Throws an exception when component's controller is defined but not exists
     * @param string $componentName
     * @return \Sway\Component\Init\Exception\ComponentLoaderException
     */
    public static function componentControllerNotExists(string $componentName) : ComponentLoaderException
    {
        return (new ComponentLoaderException(sprintf("Controller is defined, but not exists for '%s' component", $componentName)));
    }
}

?>

