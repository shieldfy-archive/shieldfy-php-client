<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;

class APIMonitor extends MonitorBase
{
    use Judge;
    protected $name = 'api';
    protected $request;

    protected $score = 0;
    protected $ruleIds = [];
    protected $infection = [];

    /**
     * run the monitor
     */
    public function run()
    {
        $this->request = $this->collectors['request'];
        $this->score = $requestScore = $this->request->getScore();
        $this->issue('api');
        $this->checkForJWTViolation();
        $this->checkForOAuthViolation();
        if ($this->score > $requestScore) {
            $this->handle([
                'score' => $this->score,
                'infection' => $this->infection
            ]);
        }
    }

    public function checkForJWTViolation()
    {
        //check for headers
        if (isset($this->request->server['HTTP_AUTHORIZATION']) && strpos($this->request->server['HTTP_AUTHORIZATION'], 'Bearer') !== false) {
            //check if it jwt structure
            $jwt_token = $this->request->server['HTTP_AUTHORIZATION'];

            //check for none algorithm (alg == 'none')
            $jwt_token = str_replace('Bearer', '', $jwt_token);

            $tokenParts = explode('.', $jwt_token);
            if (count($tokenParts) < 2) {
                return;
            }

            //exit;
            $algorithm = @json_decode(base64_decode($tokenParts[0]), 1)['alg'];

            $result = $this->sentence($algorithm, 'JWT:ALG');
            if ($result['score']) {
                $this->score += $result['score'];
                $this->infection['server.HTTP_AUTHORIZATION'] = $result['ruleIds'];
            }
        }
    }

    public function checkForOAuthViolation()
    {
        if (isset($this->request->get['response_type'])) {
            //traditional oAuth request
            $result = $this->sentence($this->request->get['response_type'], 'OAUTH:RESPONSE_TYPE');
            if ($result['score']) {
                $this->score += $result['score'];
                $this->infection['get.response_type'] = $result['ruleIds'];
            }
        }
        if (isset($this->request->get['redirect_uri'])) {
            $result = $this->sentence($this->request->get['redirect_uri'], 'OAUTH:REDIRECT_URI');
            if ($result['score']) {
                $this->score += $result['score'];
                $this->infection['get.redirect_uri'] = $result['ruleIds'];
            }
        }
    }
}
