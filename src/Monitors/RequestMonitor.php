<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;

class RequestMonitor extends MonitorBase
{
    use Judge;

    protected $name = "request";

    /**
     * run the monitor
     * Monitor for bots traditional attacks
     * ex: general heavy zero days RCE
     * Increase request sensetivity for next monitors
     */
    public function run()
    {
        /*
        *  HTTP POLLUTION
        *  CRLF INJECTION
        */
        $request = $this->collectors['request'];
        $info = $request->getInfo();
        $this->issue('request');

        $judgment = [
            'score'=>0,
            'infection'=>[]
        ];

        foreach ($info['get'] as $key => $value) {
            //	$value  = $this->normalize($value);
            $result = $this->sentence($value);
            if ($result['score']) {
                $judgment['score'] += $result['score'];
                $judgment['infection'][$key] = $result['ruleIds'];
            }
        }

        foreach ($info['post'] as $key => $value) {
            $result = $this->sentence($value);
            if ($result['score']) {
                $judgment['score'] += $result['score'];
                $judgment['infection'][$key] = $result['ruleIds'];
            }
        }

        //update request sensetivity
        $request->setScore($judgment['score']);

        $this->handle($judgment);
    }
}
