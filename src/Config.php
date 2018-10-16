<?php
namespace Shieldfy;

use ArrayAccess;

class Config implements ArrayAccess
{
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new configuration container.
     *
     * Merge the user configurations with the default config
     *
     * @param array $defaults
     * @param array $userConfig
     *
     * @return void
     */
    public function __construct(array $userConfig = [])
    {
        $this->items = array_replace_recursive($this->getDefaults(), $userConfig);
    }

    /**
     * Get default configurations
     */
    public function getDefaults()
    {
        $defaults = [
            "app_key"           => "",
            "app_secret"        => "",
            "debug"             => false,
            "action"            => "block",
            "blockPage"         => null,
            "headers"           => [
                "X-XSS-Protection"       =>  "1; mode=block",
                "X-Content-Type-Options" =>  "nosniff",
                "X-Frame-Options"        =>  "SAMEORIGIN"
            ],
            "disable"           =>  [],
            "scanFiles"     => ['composer.json','composer.lock']
        ];

        // Overwrite env if exists.
        if (getenv('SHIELDFY_APP_KEY')) {
            $defaults['app_key'] = getenv('SHIELDFY_APP_KEY');
        }
        if (getenv('SHIELDFY_APP_SECRET')) {
            $defaults['app_secret'] = getenv('SHIELDFY_APP_SECRET');
        }
        if (getenv('SHIELDFY_DEBUG')) {
            $defaults['debug'] = getenv('SHIELDFY_DEBUG');
        }
        if (getenv('SHIELDFY_ACTION')) {
            $defaults['action'] = getenv('SHIELDFY_ACTION');
        }

        $defaults['paths'] = [
            'base'      => $this->getBaseDirectory(),
            'root'      =>  realpath(__DIR__.DIRECTORY_SEPARATOR.'..'),
            'src'       =>  __DIR__,
            'data'      =>  __DIR__.DIRECTORY_SEPARATOR.'Data',
            'logs'      =>  realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'logs',
            'vendors'   =>  $this->getVendorsDir()
        ];

        return $defaults;
    }


    public function getVendorsDir()
    {
        $vendorsDir = explode(DIRECTORY_SEPARATOR, __DIR__);
        $vendorsDir = (array_slice(explode(DIRECTORY_SEPARATOR, __DIR__), 0, count($vendorsDir) - 3));
        $vendorsDir = implode(DIRECTORY_SEPARATOR, $vendorsDir);

        if (!file_exists($vendorsDir.DIRECTORY_SEPARATOR.'autoload.php')) {
            $reflector = new \ReflectionClass('Composer\Autoload\ClassLoader');
            $vendorsDir = realpath(dirname($reflector->getFileName()).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
        }
        return $vendorsDir;
    }

    /**
     * Retrieve base directory.
     */
    public function getBaseDirectory()
    {

        // -- first method --
        //
        // Search stack to find the original folder from the Composer folder.
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($stack as $frame) {
            if ($frame['function'] == 'getLoader') {
                $baseDirectory1 =  realpath(dirname($frame['file']).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
                break;
            }
        }

        if (isset($baseDirectory1) && file_exists($baseDirectory1.DIRECTORY_SEPARATOR.'composer.json')) {
            return $baseDirectory1;
        }

        // -- second method --
        $firstFrame = $stack[count($stack) - 1];
        $baseDirectory2 = dirname($firstFrame['file']);
        if (file_exists($baseDirectory2.DIRECTORY_SEPARATOR.'composer.json')) {
            return $baseDirectory2;
        }

        // -- third method --
        $baseDirectory3 =  realpath($baseDirectory2.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
        if (file_exists($baseDirectory3.DIRECTORY_SEPARATOR.'composer.json')) {
            return $baseDirectory3;
        }

        return false;
    }

    /**
     * Set configuration.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Check whether key exists.
     *
     * @param type $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->items[$key]);
    }

    /**
     * Remove config item.
     *
     * @param type $key
     *
     * @return bool
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Get config item.
     *
     * @param type $key
     *
     * @return mixed value
     */
    public function offsetGet($key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }
}
