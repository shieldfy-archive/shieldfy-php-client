<?php

namespace Shieldfy\Sniffer;

use Closure;

class Sniffer
{
    protected $types = [];

    /**
     * Detect constructor.
     */
    public function __construct()
    {
        $this->types = [
            'number'   => \Shieldfy\Sniffer\Types\NumberType::class,
            'string'    => \Shieldfy\Sniffer\Types\StringType::class,
            'json'      => \Shieldfy\Sniffer\Types\JsonType::class,
            'serialize' => \Shieldfy\Sniffer\Types\SerializeType::class,
        ];
    }

    /**
     * Defines which types to use to overwrite the default ones.
     *
     * @param array $types
     */
    public function use(array $types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Register new type on the runtime.
     *
     * @param string $name
     * @param string $class
     */
    public function register($name, $class)
    {
        $this->types[$name] = $class;

        return $this;
    }

    /**
     * Start sniffing the content.
     *
     * @param mixed $input
     *
     * @return $type
     */
    public function sniff($input)
    {
        if (is_array($input)) {
            return $this->sniffAll($input);
        }

        return $this->run($input);
    }

    /**
    * test against particular type
    * 
    * @param mixed $input
    *
    * @return boolean
    */
    public function is($input,$type)
    {
        if(!isset($this->types[$type]))
            throw new \Exception("Type Not found use one of supported types ( ".implode(' , ',array_keys($this->types))." )");
        $typeClass = $this->types[$type];
        return (new $typeClass())->sniff($input);            
    }

    /**
     * Start Sniffing array.
     *
     * @param array $inputs
     *
     * @return array $result
     */
    private function sniffAll(array $inputs)
    {
        $result = [];
        foreach ($inputs as $key => $input):
            $result[$key] = $this->run($input);
        endforeach;

        return $result;
    }

    /**
     * Run the tests on the input.
     *
     * @param mixed $input
     *
     * @return mixed result
     */
    private function run($input)
    {
        foreach ($this->types as $name => $typeClass):

            //check if it custom type closure
            if (is_object($typeClass) && $typeClass instanceof Closure) {
                if ($typeClass($input) === true) {
                    return $name;
                }
                continue;
            }

        if ((new $typeClass())->sniff($input) === true) {
            return $name;
        }

        endforeach;
        //nothing captuared
        return 'unknown';
    }
}
