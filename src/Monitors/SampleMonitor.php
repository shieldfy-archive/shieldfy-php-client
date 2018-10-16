<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;
use Shieldfy\Collectors\RequestCollector;

class SampleMonitor extends MonitorBase
{
    use Judge;

    protected $name = "sample";

    /**
     * Run the monitor.
     */
    public function run()
    {
        $this->sendToJail('high', [
            'score' => 0,
            'rulesIds' => []
        ]);
    }
}
