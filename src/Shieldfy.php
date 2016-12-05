<?php
namespace Shieldfy;


class Shieldfy
{
    /**
     * @const string VERSION API version
     * @const string DIR current directory of class
     * */
    const VERSION = '0.1';


    /**
     * @var string $rootDir current directory
     * @var string $appKey
     * @var string $appSecret
     * @var string[] $disabledHeaders
     * @var mixed[] $config Default class configuration
     */

    protected static $rootDir;
    protected static $appKey;
    protected static $appSecret;
    protected static $disabledHeaders = [];
    protected static $config = [
        'debug'=>false,
        'action'=>'block',
        'disabledHeaders'=>[]
    ];

    /**
     * @var string $endpoint
     */

    public static $endpoint = 'http://api.shieldfy.io/v1';


    /**
     * Sets user defined class config.
     * @param Array $config
     * @return Array merged config $config
     */

    public static function setConfig(Array $config)
    {
        self::setApiKey([
            'app_key' => $config['app_key'],
            'app_secret' => $config['app_secret']
        ]);

        self::mergeConfig($config);
        self::setRootDir(__dir__);
        return self::$config;
    }

    /**
     * Set root dir for the package
     * @param string $rootDir 
     * @return void
     */
    public static function setRootDir($rootDir)
    {
        self::$rootDir = $rootDir;
    }

    /**
     * get the root dir for the package
     * @return string $rootDir
     */
    public static function getRootDir()
    {
        return self::$rootDir;
    }

    /**
     * Merge user config with default config
     * @param Array $config
     * @return void
     */

    public static function mergeConfig(Array $config){
        self::$config = array_replace_recursive(self::$config, $config);
    }

    /**
     * Sets user defined API key/secret.
     * @param Array $api
     * @return type
     */

    public static function setApiKey(Array $api)
    {
        self::$appKey = $api['app_key'];
        self::$appSecret = $api['app_secret'];
    }

    /**
     * Returns an array of app key/secret.
     * @return string[]
     */
    public static function getAppKeys(){
        return [
            'app_key'=>self::$appKey,
            'app_secret'=>self::$appSecret
        ];
    }

    /**
     * Return API version.
     * @return string
     */

    public static function getApiVersion(){
        return self::VERSION;
    }

    /**
     * Returns configuration
     * @return mixed[] $config
     */

    public static function getConfig()
    {
        return self::$config;
    }
}
