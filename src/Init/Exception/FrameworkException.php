<?php

namespace Sway\Component\Init\Exception;

class FrameworkException extends \Exception
{
    /**
     * Throws an exception when given framework running mode is unknown
     * @param string $mode
     * @return \Sway\Component\Init\Exception\FrameworkException
     */
    public static function unknownFrameworkRunningMode(string $mode) : FrameworkException
    {
        return (new FrameworkException("Specified framework running mode '%s' is unknown"));
    }
    
    /**
     * Throws an exception when framework running mode is undefinded
     * @return \Sway\Component\Init\Exception\FrameworkException
     */
    public static function undefindedFrameworkRunningMode() : FrameworkException
    {
        return (new FrameworkException("Framework running mode is not specified!"));
    }
}


?>