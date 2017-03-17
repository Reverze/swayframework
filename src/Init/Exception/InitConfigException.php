<?php

namespace Sway\Component\Init\Exception;

class InitConfigException extends \Exception
{
    /**
     * Throws an exception when key is missing in configuration file
     * @param string $missedKey
     * @param string $path
     * @return \Sway\Component\Init\Exception\InitConfigException
     */
    public static function missedKey(string $missedKey, string $path) : InitConfigException
    {
        return (new InitConfigException(sprintf("Key %s is missing in configuration file %s", 
                $missedKey, $path)));
    }
    
    /**
     * Throws an exception when variable has not been found
     * @param string $variableName
     * @return \Sway\Component\Init\Exception\InitConfigException
     */
    public static function variableNotFound(string $variableName) : InitConfigException
    {
        return (new InitConfigException(sprintf("Variable '%s' not found in configuration", $variableName)));
    }
}

?>