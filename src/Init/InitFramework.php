<?php

namespace Sway\Component\Init;

use Sway\Component\Dependency\DependencyInterface;
use Sway\Component\Dependency\DependencyInjector;
use Sway\Distribution\FrameworkDistribution;
use Sway\Component\Console;
use Rev\ExPage;


class InitFramework extends DependencyInterface
{
    /**
     * Dependency injector
     * @var \Sway\Component\Dependency\DependencyInjector 
     */
    private $dependencyInjector = null;
    
    /**
     * Application's working directory
     * @var string
     */
    private $applicationWorkingDirectory = null;
    
    /**
     * Framework's working directory
     * @var string
     */
    private $frameworkWorkingDirectory = null;
    
    /**
     *
     * @var \Sway\Component\Init\Config
     */
    private $initConfig = null;
    
    /**
     * Component's loader
     * @var \Sway\Component\Init\ComponentLoader
     */
    private $componentLoader = null;
    
    /**
     * Framework distribution
     * @var \Sway\Distribution\FrameworkDistribution
     */
    private $frameworkDistribution = null;
    
    /**
     * Framework running mode (dev|prod|test)
     * @var string
     */
    private $frameworkMode = null;
    
    /**
     * Stores the state of framework 
     * @var bool
     */
    private $frameworkInited = false;
    
    /**
     * Storage channel with configuration of application, framework and vendors
     * @var \Sway\Distribution\Storage\Channel
     */
    private $configurationChannel = null;

    /**
     * Error pages manager
     * @var \Rev\ExPage\Manager
     */
    private $exPage = null;
    
    public function __construct(string $applicationWorkingDirectory, string $frameworkWorkingDirectory)
    {
        if (!empty($applicationWorkingDirectory)){
            $this->applicationWorkingDirectory = $applicationWorkingDirectory;
        }
        
        if (!empty($frameworkWorkingDirectory)){
            $this->frameworkWorkingDirectory = $frameworkWorkingDirectory;
        }
        
        $this->frameworkDistribution = new FrameworkDistribution($frameworkWorkingDirectory);
       
    }
    
    /**
     * Inits distribution
     */
    public function initDistribution()
    {
        
       $this->frameworkDistribution->initDistribution();
       
       /**
        * Prepares parameters for framework service
        */
       $frameworkServiceParameters = [
           'app_working_directory' => $this->applicationWorkingDirectory,
           'vendor_directory' => sprintf('%s/vendor', $this->applicationWorkingDirectory),
           'service_container' => $this->getDependency('serviceContainer'),
           'parameter_container' => $this->getDependency('parameter')
       ];
       
       $this->frameworkDistribution->initializeFrameworkService($frameworkServiceParameters);
       
       
    }
    
    /**
     * Gets application working directory
     * @return string
     */
    public function getApplicationWorkingDirectory() : string
    {
        return $this->applicationWorkingDirectory;
    }
    
    /**
     * Gets framework working directory
     * @return string
     */
    public function getFrameworkWorkingDirectory() : string
    {
        return $this->frameworkWorkingDirectory;
    }

    /**
     * Initializes ExPage extension (only in 'dev' mode)
     */
    protected function initializeExPage()
    {
        $this->exPage = new ExPage\Manager([
            'dirname' => sprintf("%s/tmp/expage_logs/", $this->applicationWorkingDirectory),
            'filelog' => 'default.log',
            'template' => 'default',
            'separate' => [
                'errors' => 'errors.log',
                'exceptions' => 'exceptions.logs'
            ],
            'mode' => 'dev',
            'cli_view' => [
                'error' => [
                    'show_file' => true,
                    'show_line' => true,
                    'show_scope' => true
                ],
                'exception' => [
                    'show_file' => true,
                    'show_line' => true,
                    'show_trace' => true
                ]
            ]
        ]);
    }
    
    /**
     * Inits standard framework configuration
     */
    protected function initInitConfig()
    {
        $initConfig = new Config();
        $this->dependencyInjector->inject($initConfig);
        $this->dependencyInjector->createDependency('config', $initConfig);
        
        $this->initConfig = $initConfig;

        /**
         * ExPage runs only in 'dev' mode
         */
        if ($this->getRunningMode() === 'dev'){
            $this->initializeExPage();
        }

        /**
         * If framework is running in production mode
         */
        if ($this->getRunningMode() === 'prod'){
            /**
             * We must check if framework and vendors configuration is stored
             */
            $frameworkConfigurationChannel = $this->frameworkDistribution->getContainerBuilder()->getFrameworkConfigurationChannel();
            
            /**
             * If configuration is not stored
             */
            if (!$frameworkConfigurationChannel->has('_framework_magic')){
                /**
                 * Initialize standard configuration (only framework, not vendors). We assume that composer 
                 * has stored vendors configuration
                 */
                $this->initConfig->initializeStandard();
                
                /**
                 * Stored as array
                 */
                $frameworkConfig = $this->initConfig->getFrameworkConfig();
                
                /**
                 * Adds some magic fields
                 */
                $frameworkConfig['_framework_magic']['createdat'] = time();
                $frameworkConfig['_framework_magic']['createdatenv'] = (isset($_SERVER['HTTP_HOST']) ? 
                        (empty($_SERVER['HTTP_HOST']) ? 'cli' : 'httpd') : 'cli');
                $frameworkConfig['_framework_magic']['creator.httpd.host'] = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
                $frameworkConfig['_framework_magic']['running_mode'] = 'prod';
                
                $this->frameworkDistribution->getContainerBuilder()->assert($frameworkConfig);
            }
            
        }
        
        /**
         * If framework is running in development mode
         */
        if ($this->getRunningMode() === 'dev'){
            /**
             * We enable transitory storage, so every changes will not reflect stored vendors configuration.
             * Framework configuration will be stored only at memory
             */
            $this->frameworkDistribution->getContainerBuilder()->transitoryStorage();
            /**
             * We must check if framework and vendors configuration is stored
             */
            $frameworkConfigurationChannel = $this->frameworkDistribution->getContainerBuilder()->getFrameworkConfigurationChannel();
            
            $this->initConfig->initializeStandard();
            
            $frameworkConfig = $this->initConfig->getFrameworkConfig();
            
            /**
             * Like previous, adds some magic fields
             */
            $frameworkConfig['_framework_magic']['createdat'] = time();
            $frameworkConfig['_framework_magic']['createdatenv'] = (isset($_SERVER['HTTP_HOST']) ? 
                    (empty($_SERVER['HTTP_HOST']) ? 'cli' : 'httpd') : 'cli');
            $frameworkConfig['_framework_magic']['creator.httpd.host'] = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
            $frameworkConfig['_framework_magic']['running_mode'] = 'dev';
            
            $this->frameworkDistribution->getContainerBuilder()->assert($frameworkConfig);
        }          
    }
    
    public function initExtendedConfig(string $relativePath)
    {
        if ($this->getRunningMode() === 'prod'){
            
            /**
             * Gets configuration channel for current running application
             */
            $applicationConfigurationChannel = $this->frameworkDistribution->getContainerBuilder()->getApplicationConfigurationChannel(sprintf("%s/%s",
                    $this->getApplicationWorkingDirectory(), $relativePath));
            
            /**
             * If application configuration is not stored
             */
            if (!$applicationConfigurationChannel->has('_framework_app_magic')){
                /**
                 * Reads configuration of application
                 */
                $this->initConfig->initializeExtended($relativePath);
                
                /**
                 * Application configuration is a configuration which 'extends' framework and vendors configuration
                 */
                $applicationConfiguration = $this->initConfig->getExtendedConfig();
                
                if (isset($applicationConfiguration['application']['identifier'])){
                    $applicationConfiguration['_framework_app_magic']['app.identifier'] = $applicationConfiguration['application']['identifier'];
                }
                $applicationConfiguration['_framework_app_magic']['app.cfg_path'] = $relativePath;
                $applicationConfiguration['_framework_app_magic']['app.cwd'] = $this->getApplicationWorkingDirectory();
                $applicationConfiguration['_framework_app_magic']['createdat'] = time();
                
                
                $this->frameworkDistribution->getContainerBuilder()->assertApp($applicationConfiguration, sprintf("%s/%s",
                        $this->getApplicationWorkingDirectory(), $relativePath));
            }
        }
        
        if ($this->getRunningMode() === 'dev'){
            /**
             * We enable transitory storage, so every changes will not reflect stored vendors configuration.
             * Framework configuration will be stored only at memory
             */
            $this->frameworkDistribution->getContainerBuilder()->transitoryStorage();
            
            $applicationConfigurationChannel = $this->frameworkDistribution->getContainerBuilder()->getApplicationConfigurationChannel(sprintf("%s/%s",
                    $this->getApplicationWorkingDirectory(), $relativePath));
            
            $this->initConfig->initializeExtended($relativePath);
                
            /**
                 * Application configuration is a configuration which 'extends' framework and vendors configuration
                 */
            $applicationConfiguration = $this->initConfig->getExtendedConfig();


            if (isset($applicationConfiguration['application']['identifier'])){
                $applicationConfiguration['_framework_app_magic']['app.identifier'] = $applicationConfiguration['application']['identifier'];
            }
            $applicationConfiguration['_framework_app_magic']['app.cfg_path'] = $relativePath;
            $applicationConfiguration['_framework_app_magic']['app.cwd'] = $this->getApplicationWorkingDirectory();
            $applicationConfiguration['_framework_app_magic']['createdat'] = time();

            $this->frameworkDistribution->getContainerBuilder()->assertApp($applicationConfiguration, 
                    sprintf("%s/%s", $this->getApplicationWorkingDirectory(), $relativePath));
        }
        
        $this->configurationChannel = $this->frameworkDistribution->getContainerBuilder()->getApplicationConfigurationChannel(sprintf("%s/%s",
                    $this->getApplicationWorkingDirectory(), $relativePath));         
    }
    
    protected function dependencyController() 
    {
        /**
         * Stores dependency injector into class 
         */
        $this->dependencyInjector = $this->getDependency('injector');
        
        $this->dependencyInjector->createDependency('framework', $this);
    }
    
    /**
     * Gets variable's value specified in configuration
     * @param string $variablePath
     * @return mixed
     */
    public function getCfg(string $variablePath)
    {
        //return $this->initConfig->get($variablePath);
        return $this->configurationChannel->get($variablePath);
    }
    
    /**
     * Checks if variable is defined in configuration
     * @param string $variablePath
     * @return bool
     */
    public function hasCfg(string $variablePath) : bool
    {
        //return $this->initConfig->has($variablePath);
        return $this->configurationChannel->has($variablePath);
    }
    
    /**
     * Sets framework running mode
     * @param string $mode
     * @throws \Sway\Component\Init\Exception\FrameworkException
     */
    public function setRunningMode(string $mode)
    {
        if (strtolower($mode) === 'dev' || strtolower($mode) === 'prod' ||
                strtolower($mode) === 'test'){
            $this->frameworkMode = strtolower($mode);
        }    
        /**
         * If given mode is not supported
         */
        else{
            throw Exception\FrameworkException::unknownFrameworkRunningMode($mode);
        }
    }
    
    /**
     * Gets framework running mode
     * @return string
     */
    public function getRunningMode() : string
    {
        /**
         * If framework mode is undefinded
         */
        if (empty($this->frameworkMode)){
            throw Exception\FrameworkException::undefindedFrameworkRunningMode();
        }
        
        return $this->frameworkMode;
    }
    
    public function initConfig()
    {
        $this->initInitConfig();
    }
    
    public function initStandardFeatures()
    {
        $autoloaderInit = new InitAutoloader();
        $this->dependencyInjector->inject($autoloaderInit);
        $container = $autoloaderInit->init();   
        
        $this->dependencyInjector->createDependency('autoloader', $container);
        /**
         * If section autoloader in configuration is not defined, getCfg will return null
         */
        $autoloaderCfg = $this->getCfg('framework/autoloader');
        $this->getDependency('autoloader')->appendFrom(is_array($autoloaderCfg) ? $autoloaderCfg : array());
        
        
    }
    
    public function initComponents()
    {
        $this->componentLoader = ComponentLoader::create();
        $this->dependencyInjector->inject($this->componentLoader);
        /**
         * Example
         * $eventListener = new \Sway\Component\Event\EventListener(function(\Sway\Component\Event\EventArgs $args, $object){
            
        }
        );
        $this->getDependency('event')->listenOn('FrameworkInited', $eventListener);*/
        $this->frameworkInited = true;
    }
    
    public function flush()
    {
        if ($this->frameworkInited){
            $this->getDependency('event')->trigger('FrameworkInited', new \Sway\Component\Event\EventArgs(), $this);
        } 
    }
    
    public function runConsoleSession(Console\Input\ArgvInput $input)
    {
        if (!$this->getDependency('serviceContainer')->has('console')){
            throw new \Exception ("Console extension seems to be not installed. Service 'console' was not found");
        }
          
        $consoleService = $this->getDependency('serviceContainer')->get('console');        
        $consoleService->setInput($input);
        $consoleService->execute();
    }
    
}


?>

