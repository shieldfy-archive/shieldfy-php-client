<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;
use Shieldfy\Collectors\RequestCollector;

class RequestMonitor extends MonitorBase
{
    use Judge;

    protected $name = "request";

    /**
     * run the monitor
     */
    public function run()
    {
        $request = $this->collectors['request'];
        $user = $this->collectors['user'];

        $result = $this->checkForCSRF($request);
    }

    private function checkForCSRF(RequestCollector $request)
    {
        $request = $this->collectors['request'];
        
        if ($request->requestMethod !== 'POST') {
            return false;
        }

        if (!isset($request->server['HTTP_ORIGIN'])) {
            return false;
        }

        if (strpos($request->server['HTTP_ORIGIN'], 'http') !== 0) {
            $request->server['HTTP_ORIGIN'] = 'http://'.$request->server['HTTP_ORIGIN'];
        }

        if (strpos($request->server['HTTP_HOST'], 'http') !== 0) {
            $request->server['HTTP_HOST'] = 'http://'.$request->server['HTTP_HOST'];
        }

        $origin = parse_url(trim($request->server['HTTP_ORIGIN']), PHP_URL_HOST);
        $host = parse_url(trim($request->server['HTTP_HOST']), PHP_URL_HOST);


        if (strtolower($origin) !== strtolower($host)) {
            //csrf attack found
            //since most of frameworks now uses csrf token & many of endpoint are ajax/api
            //and modern browsers default block violation of origin ( CORS : https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS )
            //so kind of violation of origin is not critical but worth reporting to the developer
            //so he can fix anything if needed
            $this->sendToJail('low', [
                'score' => 30,
                'rulesIds' => [300],
                'infection'  => [
                    'server.HTTP_ORIGIN' => $origin
                ]
            ]);
        }
    }
}
