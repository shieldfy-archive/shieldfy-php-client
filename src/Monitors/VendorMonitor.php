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
            $path_info = (isset($request->server['PATH_INFO'])) ? $request->server['PATH_INFO'] : '';
            $charge = $this->sentence($value, $path_info . ':' . $request->requestMethod . ':' . $key);
            $severity = $this->parseScore($charge['score']);
            if ($charge && $charge['score']) {
                $this->sendToJail($severity, $charge);
            }
        }
    }
}
