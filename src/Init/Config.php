<?php

namespace Sway\Component\Init;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Init\Exception;

class Config extends DependencyInterface
{
    /**
     * 
     * @var \Sway\Component\Init\InitFramework
     */
    private $framework = null;
    
    /**
     * Relative path to standard framework config file
     * @var string
     */
    private $standardFrameworkConfigPath = null;
    
    /**
     * Contains only standard framework config
     * @var array
     */
    private $frameworkConfig = null;
    
    /**
     * Extended framework config
     * @var array
     */
    private $extendedConfig = null;
    
    /**
     * Full framework config
     * @var array
     */
    private $config = null;
    
    public function __construct() 
    {
        if (empty($this->standardFrameworkConfigPath)){
            $this->standardFrameworkConfigPath = '/etc/framework/config.yml';
        }
    }
    
    protected function dependencyController() 
    {
        $this->framework = $this->getDependency('framework');
    }
    
    /**
     * Initializes standards framework config
     * @throws \Sway\Component\Init\Exception\InitConfigException
     */
    public function initializeStandard()
    {
        /**
         * Absolute path to standard config file
         */
        $absoluteStandardConfigPath = realpath(sprintf("%s%s",
                $this->framework->getFrameworkWorkingDirectory(),
                $this->standardFrameworkConfigPath));
        
        if (!$absoluteStandardConfigPath){
            $absoluteStandardConfigPath = realpath(sprintf("%s%s",
                $this->framework->getFrameworkWorkingDirectory(),
                '/app/framework/config.yml'));
        }
        
        $yamlWrapper = new \YamlWrapper();
        $yamlWrapper->setFile($absoluteStandardConfigPath);
        
        $config = $yamlWrapper->parse();
        
        /**
         * Key 'framework' is required
         */
        if (!isset($config['framework'])){
            throw Exception\InitConfigException::missedKey('framework', $absoluteStandardConfigPath);
        }
        
        $this->frameworkConfig = $config; 
    }
    
    /**
     * Initialze extended framework configuration file
     * @param string $relativePath
     */
    public function initializeExtended(string $relativePath)
    {
        
        /**
         * Absolute to extended config file (application config)
         */
        $absoluteExtendedConfigPath = realpath(sprintf("%s/%s",
                $this->framework->getApplicationWorkingDirectory(),
                $relativePath));
        
        $yamlWrapper = new \YamlWrapper();
        $yamlWrapper->setFile($absoluteExtendedConfigPath);
        
        $config = $yamlWrapper->parse();
        
        $this->extendedConfig = $config;
            
    }
    
    public function get(string $variablePath)
    {
        /**
         * Exploded variable's path
         */
        $explodedVariablePath = explode('/', $variablePath);
        
        /**
         * Value of variable
         */
        $variableValue = null;
        
        /** Size of path variable */
        $variablePathSize = sizeof($explodedVariablePath);
        
        try {
            $variableValue = $this->getVariableFrom($this->extendedConfig, $explodedVariablePath);   
            
            if (is_array($variableValue)){
                try {
                    $baseValue = $this->getVariableFrom($this->frameworkConfig, $explodedVariablePath);

                    foreach ($baseValue as $key => $val) {
                        if (!isset($variableValue[$key])) {
                            $variableValue[$key] = $val;
                        }
                    }
                } catch (Exception\InitConfigException $ex) {
                    //do nothing at this case
                }
            }
        } 
        catch (Exception\InitConfigException $ex) {
            try{
                $variableValue = $this->getVariableFrom($this->frameworkConfig, $explodedVariablePath);
            } 
            catch (Exception\InitConfigExceptio $ex2) {
                throw $ex2;
            }
        }
        
        return $variableValue;
    }
    
    /**
     * Checks if variable is defined
     * @param string $variablePath
     * @return boolean
     */
    public function has(string $variablePath)
    {
        try{
            $this->get($variablePath);
            return true;
        } catch (Exception\InitConfigException $ex) {
            return false;
        }
    }
    
    /**
     * Gets variable from passed array by variable path
     * @param array $lookin
     * @param array $variablePath
     * @return mixed
     * @throws \Sway\Component\Init\Exception\InitConfigException
     */
    private function getVariableFrom(array &$lookin, array $variablePath)
    {
        /**
         * Value of searched variable
         */
        $variableValue = null;
        
        /** Size of variable path array */
        $pathSize = sizeof($variablePath);
       
        for ($pointer = 0; $pointer < $pathSize; $pointer++){        
            if ($pointer === 0){
                $variableValue = $lookin[$variablePath[$pointer]];    
            }
            else{
                if (isset($variableValue[$variablePath[$pointer]])){
                    $variableValue = $variableValue[$variablePath[$pointer]];
                }
                else{
                    throw Exception\InitConfigException::variableNotFound($variablePath[$pointer]);
                }
            }
            
        }
        
        return $variableValue;
    }
    
    /**
     * Gets framework config
     * @return array
     */
    public function getFrameworkConfig() : array
    {
        return $this->frameworkConfig;
    }
    
    /**
     * Gets config extended by application
     * @return array
     */
    public function getExtendedConfig()
    {
        return $this->extendedConfig;
    }
    
    
}


?>

