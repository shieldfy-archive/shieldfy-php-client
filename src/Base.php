<?php

namespace Shieldfy;

class Base
{
    /**
     * @const string VERSION API version
     * @const string DIR current directory of class
     * */
    const VERSION = '0.1';

    /**
     * @var string   current directory
     * @var string   $appKey
     * @var string   $appSecret
     * @var string[] $disabledHeaders
     * @var mixed[]  $config Default class configuration
     */
    protected $rootDir;
    protected $appKey;
    protected $appSecret;
    protected $disabledHeaders = [];
    protected $config = [
        'debug'          => false,
        'action'         => 'block',
        'disabledHeaders'=> [],
    ];

    /**
     * @var string
     */
    public $endpoint = 'http://api.shieldfy.io/v1';

    /**
     * Sets user defined class config.
     *
     * @param array $config
     *
     * @return array merged config $config
     */
    public function setConfig(array $config)
    {
        self::setApiKey([
            'app_key'    => $config['app_key'],
            'app_secret' => $config['app_secret'],
        ]);

        self::mergeConfig($config);
        self::setRootDir(__dir__);

        return self::$config;
    }

    /**
     * Set root dir for the package.
     *
     * @param string $rootDir
     *
     * @return void
     */
    public function setRootDir($rootDir)
    {
        self::$rootDir = $rootDir;
    }

    /**
     * get the root dir for the package.
     *
     * @return string $rootDir
     */
    public function getRootDir()
    {
        return self::$rootDir;
    }

    /**
     * Merge user config with default config.
     *
     * @param array $config
     *
     * @return void
     */
    public function mergeConfig(array $config)
    {
        self::$config = array_replace_recursive(self::$config, $config);
    }

    /**
     * Sets user defined API key/secret.
     *
     * @param array $api
     *
     * @return type
     */
    public function setApiKey(array $api)
    {
        self::$appKey = $api['app_key'];
        self::$appSecret = $api['app_secret'];
    }

    /**
     * Returns an array of app key/secret.
     *
     * @return string[]
     */
    public function getAppKeys()
    {
        return [
            'app_key'   => self::$appKey,
            'app_secret'=> self::$appSecret,
        ];
    }

    /**
     * Return API version.
     *
     * @return string
     */
    public function getApiVersion()
    {
        return self::VERSION;
    }

    /**
     * Returns configuration.
     *
     * @return mixed[] $config
     */
    public function getConfig()
    {
        return self::$config;
    }
}
