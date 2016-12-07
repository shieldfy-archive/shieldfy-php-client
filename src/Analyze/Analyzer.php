<?php

namespace Shieldfy\Analyze;

use Shieldfy\Cache\CacheInterface;
use Shieldfy\Config;
use Shieldfy\Normalizer\Normalizer;
use Shieldfy\Request;
use Shieldfy\Session;

class Analyzer
{
    const DANGEROUS = 1;
    const SUSPICIOUS = 2;
    const CLEAN = 0;

    protected $config;
    protected $session;
    protected $cache;

    private $skippedKeys = [
        'server.ps',
        'server.uri',
        'server.hh',
        'server.ho',
        'server.r',
    ];
    protected $user;
    protected $request;
    protected $history;
    protected $result = [
        'status'      => self::CLEAN,
        'total_score' => 0,
        'request'     => [],

    ];

    public function __construct(Session $session, CacheInterface $cache, Config $config)
    {
        $this->session = $session;

        //load user from session
        $sessionInfo = $this->session->getInfo();
        $this->user = $sessionInfo['user'];
        $this->request = $sessionInfo['request'];
        $this->history = $sessionInfo['history'];

        $this->cache = $cache;
        $this->config = $config;
    }

    public function run()
    {

        //prepare parameters
        $params = $this->prepareRequestParameter($this->request);

        /** @todo cache  */
        $requestInfo = $this->request->getInfo();

        $requestResult = [];

        foreach ($params as $key=>$value) {
            if (in_array($key, $this->skippedKeys)) {
                continue;
            }

            //prerules
            $rules = new PreRules($this->config);
            $rules->load()->run($requestInfo['info']['method'], $key, $value);
            if ($rules->getScore() === 0) {
                continue;
            }

            //normalizer
            $value = (new Normalizer($value))->runAll();

            //softrules
            $rules = new SoftRules($this->config);
            $rules->load()->run($requestInfo['info']['method'], $key, $value);

            if ($rules->getScore() > 0) {
                $requestResult[$key] = [
                    'score' => $rules->getScore(),
                    'keys'  => $rules->getRulesIds(),
                ];
            }
        }

        //result has the request results
        $this->analyze($requestResult, $this->user->getInfo());
        //$this->prepareResult($requestResult);
    }

    private function analyze(array $requestResult = [], array $userResult = [])
    {
        $requestScore = 0;

        if (count($requestResult) === 0) {
            $this->result['status'] = self::CLEAN;

            return; //clean
        }

        $this->result['request'] = $requestResult;

        foreach ($requestResult as $param) {
            $requestScore += $param['score'];
        }

        $userScore = $userResult['score'];

        $totalScore = (($userScore + ($requestScore * 2)) / 50) * 100;

        /* save in result */
        $this->result['total_score'] = $totalScore;

        if ($totalScore >= 80) {
            $this->result['status'] = self::DANGEROUS;

            return 1; //dangerous
        }

        if ($totalScore >= 40) {
            $this->result['status'] = self::SUSPICIOUS;

            return 2; //suspicious
        }

        $this->result['status'] = self::CLEAN;
    }

    public function getResult()
    {
        return $this->result;
    }

    private function prepareRequestParameter(Request $request)
    {
        $info = $request->getInfo();

        return $this->prepareRequestParameterRecursive($info['info']['params']);
    }

    private function prepareRequestParameterRecursive($params, $prefix = '', $data = [])
    {
        foreach ($params as $key=> $value):
            if (!is_array($value)) {
                $data[$prefix.$key] = $value;
            } else {
                $data = array_merge($data, $this->prepareRequestParameterRecursive($value, $prefix.$key.'.'));
            }
        endforeach;

        return $data;
    }
}
