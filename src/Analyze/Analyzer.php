<?php

namespace Shieldfy\Analyze;

use Shieldfy\Cache;
use Shieldfy\Normalizer\Normalizer;

class Analyzer
{
    private $data;
    private $result;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function run()
    {

        /* prepare data */
        $this->prepare();

        $cache = Cache::getInstance();
        $cacheKey = md5(json_encode($this->data['request']['info']));
        if ($cache->has($cacheKey)) {
            return 0;
        }

        $params = $this->data['request']['info']['params'];

        foreach ($params as $key=>$value) {
            if (in_array($key, ['server.ps', 'server.uri', 'server.hh', 'server.ho', 'server.r'])) {
                continue;
            }

            $preResult = PreAnalyzer::run($key, $value);
            if (!$preResult) {
                continue;
            }

            $value = (new Normalizer($value))->runAll();

            $softResult = SoftRules::run($this->data['request']['info']['method'], $key, $value);

            if (!$softResult) {
                continue;
            }

            //found infection
            $this->result['request'][$key] = SoftRules::getResult();
        }
        $analyzeResult = $this->analyze();

        if ($analyzeResult) {
            return $analyzeResult;
        }

        $cache->set($cacheKey, '1');

        return 0;
    }

    private function analyze()
    {
        $requestScore = 0;

        if (!$this->result) {
            return 0; //clean
        }
        foreach ($this->result['request'] as $param) {
            $requestScore += $param['score'];
        }

        $userScore = $this->data['user']['score'];
        $totalScore = (($userScore + ($requestScore * 2)) / 50) * 100;

        /* save in result */
        $this->result['user'] = ['score'=>$userScore];
        $this->result['total_score'] = $totalScore;

        //echo '======='.$totalScore.'==========';
        if ($totalScore >= 80) {
            $this->result['status'] = 1;

            return 1; //dangerous
        }
        if ($totalScore >= 40) {
            $this->result['status'] = 2;

            return 2; //suspicious
        }

        return 0; //clean
    }

    private function prepare()
    {
        $this->data['request']['info']['params'] = $this->prep($this->data['request']['info']['params']);
    }

    private function prep($arr, $prefix = '', $data = [])
    {
        foreach ($arr as $key=> $value):
            if (!is_array($value)) {
                $data[$prefix.$key] = $value;
            } else {
                $data = array_merge($data, $this->prep($value, $prefix.$key.'.'));
            }
        endforeach;

        return $data;
    }

    public function getResult()
    {
        return $this->result;
    }
}
