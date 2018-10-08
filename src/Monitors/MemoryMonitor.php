<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;

class MemoryMonitor extends MonitorBase
{
    use Judge;

    protected $name = "memory";
    protected $infected = [];


    /**
     * Run the monitor.
     */
    public function run()
    {
        spl_autoload_register(function ($class) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            foreach ($trace as $t):
                if (isset($t['function']) && $t['function'] == 'unserialize') {
                    $this->analyze($class);
                    break;
                }
            endforeach;
        }, false);
    }

    public function analyze($object = '')
    {
        $request = $this->collectors['request'];
        $info = $request->getInfo();
        $params = array_merge($info['get'], $info['post'], $info['cookies']);

        foreach ($params as $key => $value) {
            $foundGuilty = false;
            $charge = [];
            $this->issue('memory');
            foreach ($params as $key => $value) {
                if (stripos($value, $object) !== false) {
                    //found parameter
                    //check infection
                    $charge = $this->sentence($value);

                    if ($charge && $charge['score']) {
                        $foundGuilty = true;
                        $charge['value'] = $value;
                        $charge['key'] = $key;
                        break;
                    }
                }
            }
        }

        if ($foundGuilty) {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $code = $this->collectors['code']->pushStack($stack)->collectFromStack($stack);
            $this->sendToJail('high', $charge, $code);
        }
    }
}
