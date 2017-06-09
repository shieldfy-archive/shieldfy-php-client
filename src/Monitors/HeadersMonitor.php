<?php
namespace Shieldfy\Monitors;

class HeadersMonitor extends MonitorBase
{
    protected $name = 'headers';
    /**
     * run the monitor
     */
    public function run()
    {
        ob_start(array($this,'getStatus'));
    }

    public function getStatus($content)
    {
        $statusCode = http_response_code();
        //4xx || 5xx
        if ($statusCode >= 400) {
            //just report it to session to store it as history
            $this->collectors['request']->setHttpError($statusCode);
        }
        return $content;
    }
}
