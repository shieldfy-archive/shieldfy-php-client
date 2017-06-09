<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;

class ViewMonitor extends MonitorBase
{
    use Judge;

    protected $name = 'view';

    protected $vaguePhrases = [
        '<script>','</script>'
    ];

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
            if (in_array($value, $this->vaguePhrases)) {
                continue;
            }
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
            'score'=>$request->getScore(),
            'infection'=>[]
        ];

        foreach ($suspicious as $key => $value) {
            $result = $this->sentence($value);
            $score = 0;
            $infection = [];
            
            if ($result['score']) {
                $judgment['score'] += $result['score'];
                $judgment['infection'][$key] = $result['ruleIds'];
            }
        }

        /* check for already defined files */
        $user_id = $this->collectors['user']->getId();
        $view_name = $this->cache->get($user_id.'_view_name');

        $code = $this->collectors['code']->collectFromText($content, $value);
        $code['file'] = ($view_name)? $view_name : 'none';
        $code['vulnerability'] = 1;
        
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
