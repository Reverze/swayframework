<?php

namespace Sway\Component\Dependency\Exception;

class DependencyContainerException extends \Exception
{

    /**
     * Throws an exception when dependency container is empty (is null)
     * @return \Sway\Component\Dependency\Exception\DependencyContainerException
     */
    public static function emptyDependencyContainerException() : DependencyContainerException
    {
        return (new DependencyContainerException(sprintf("Dependency container is empty (is null)")));
    }
}

?>