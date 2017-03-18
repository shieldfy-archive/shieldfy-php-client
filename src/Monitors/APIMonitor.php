<?php
namespace Shieldfy\Monitors;
use Shieldfy\Jury\Judge;
class ApiMonitor extends MonitorBase
{
	use Judge;
	protected $name = 'api';
	protected $request;

	protected $score = 0;
	protected $infection = [];

	/**
	 * run the monitor
	 */
	public function run()
	{
		$this->request = $this->collectors['request'];
        $this->checkForJWTViolation();
		$this->checkForOAuthViolation();
		if($this->score){
			$this->handle([
				'score' => $this->score,
				'infection' => $this->infection
			]);
		}
	}

    public function checkForJWTViolation()
    {
        //check for headers
        if(isset($this->request->server['HTTP_AUTHORIZATION']) && strpos($this->request->server['HTTP_AUTHORIZATION'],'Bearer') !== false){
			//check if it jwt structure
			$tokenParts = explode('.',$jwt_token);
			if(count($tokenParts) < 2) return;

            //check for none algorithm (alg == 'none')
            $jwt_token = str_replace('Bearer','',$request->server['HTTP_AUTHORIZATION']);
            $algorithm = @json_decode( base64_decode( $tokenParts[0] ),1 )['alg'];

			$result = $this->sentence($algorithm,'JWT:ALG');
			if($result['score']){
				$this->score += $result['score'];
				$this->infection['server.HTTP_AUTHORIZATION'] = $result;
			}
        }

    }

	public function checkForOAuthViolation()
	{
		if(isset($this->request->get['response_type'])){
			//traditional oAuth request
			$result = $this->sentence($this->request->get['response_type'],'OAUTH:RESPONSE_TYPE');
			if($result['score']){
				$this->score += $result['score'];
				$this->infection['get.response_type'] = $result;
			}
		}
		if(isset($this->request->get['redirect_uri'])){
			$result = $this->sentence($this->request->get['redirect_uri'],'OAUTH:REDIRECT_URI');
			if($result['score']){
				$this->score += $result['score'];
				$this->infection['get.redirect_uri'] = $result;
			}
		}
	}

}
