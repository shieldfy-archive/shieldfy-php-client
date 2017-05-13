<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;

class ViewMonitor extends MonitorBase
{
    use Judge;

    protected $name = 'view';

    /**
     * run the monitor
     */
    public function run()
    {
        ob_start(array($this,'analyzeView'));
    }

    public function analyzeView($content)
    {
        //match with the view if found
        $request = $this->collectors['request'];
        $info = $request->getInfo();
        $params = array_merge($info['get'], $info['post']);
        $suspicious = [];

        foreach ($params as $key => $value) {
            if (stripos($content, $value) !== false) {
                $suspicious[$key] = $value;
            }
        }

        //run rules on request
        if (empty($suspicious)) {
            return $content;
        }
        $this->issue('view');
        $judgment = [
            'score'=>0,
            'infection'=>[]
        ];

        foreach ($suspicious as $key => $value) {
            $result = $this->sentence($value);
            $score = 0;
            $infection = [];

            $r[] = $result;
            if ($result['score']) {
                $judgment['score'] += $result['score'];
                $judgment['infection'][$key] = $result['ruleIds'];
            }
        }

        $code = $this->collectors['code']->collectFromText($content, $value);

        $list = headers_list();
        if (in_array('X-Shieldfy-Status: blocked', $list)) {
            return $this->forceDefaultBlock($list);
        }

        $judgmentResponse = $this->handle($judgment, $code);
        if ($judgmentResponse) {
            return $judgmentResponse;
        }
        return $content;
    }
}
