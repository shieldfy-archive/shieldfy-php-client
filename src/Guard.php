<?php
namespace shieldfy;

class Guard
{
    /**
     * @var Singleton Reference to singleton class instance
     */
    private static $instance;

    /**
     * @var api endpoint
     */
    public $apiEndpoint = 'http://api.shieldfy.io/v1';

    /**
     * @var version
     */
    public $version = '2.1.0';

    /**
     * Default configurations items.
     *
     * @var array
     */
    protected $defaults = [
        'debug'          => false,
        'action'         => 'block',
        'headers'  	     => [
        	'x-xss-protection'=>'1; mode=block',
        	'x-content-type-options'=>'nosniff',
        	'x-frame-options'=>'SAMEORIGIN'
        ]
    ];

    protected $config;

    /**
     * Create a new Guard Instance
     */
    public function __construct()
    {
        
    }
}
