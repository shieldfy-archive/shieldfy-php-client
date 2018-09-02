<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;

class VendorMonitor extends MonitorBase
{
    use Judge;

    protected $name = "vendor";

    /**
     * run the monitor
     */
    public function run()
    {
        $this->issue('vendors');

        // request
        $request = $this->collectors['request'];
        $params = array_merge($request->post, $request->get);
        foreach ($params as $key => $value) {
            $charge = $this->sentence($value, $request->server['PATH_INFO'] . ':' . $request->requestMethod . ':' . $key);
            $severity = $this->parseScore($charge['score']);
            $this->sendToJail($severity, $charge);
        }
    }
}
