<?php
namespace Shieldfy\Monitors;
use Shieldfy\Config;
use Shieldfy\Session;
use Shieldfy\Cache\CacheInterface;

use Shieldfy\Dispatcher\Dispatcher;
use Shieldfy\Dispatcher\Dispatchable;

use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Response\Response;

abstract class MonitorBase implements Dispatchable
{
	use Dispatcher;
	use Exceptioner;
	use Response;
	/**
	 * @var Config $config
	 * @var CacheInterface $cache
	 * @var Array $collectors
	 */
	protected $config;
	protected $cache;
	protected $session;
	protected $collectors;
	protected $name = '';

	/**
	 * Threholds
	 */
	const LOW    = 20;
	const MEDIUM = 50;
	const HIGH   = 70;

	/**
	 * Constructor
	 * @param Config $config
	 * @param CacheInterface $cache
	 * @param array $collectors
	 */
	public function __construct(Config $config,CacheInterface $cache,Session $session, array $collectors)
	{
		$this->config = $config;
		$this->cache = $cache;
		$this->session = $session;
		$this->collectors = $collectors;
	}

	/**
	 * Force children to have its own run function
	 */
	abstract public function run();

	/**
	 * handle the judgment info
	 * @param  array $judgment judgment informatoin
	 * @return void
	 */
	protected function handle($judgment)
	{
		if($judgment['score'] < self::LOW) return; //safe

		/**
		 * report activity
		 * incidentId , host , sessionId , monitor , judgment , info , history
		 */
		$incidentId = $this->generateIncidentId($this->collectors['user']->getId());
		$this->trigger('activity',[
			'incidentId' 	=> $incidentId,
			'host' 			=> $this->collectors['request']->getHost(),
			'sessionId' 	=> $this->collectors['user']->getSessionId(),
			'monitor'		=> $this->name,
			'judgment'		=> $judgment,
			'info'			=> $this->collectors['request']->getProtectedInfo(),
			'history'		=> $this->session->getHistory()
		]);
		
		/**
		 * { incidentId: '32322611211490285296',
		 *	  host: 'php.flash.app',
		*	  sessionId: '3232261121_JG4LPV',
			*  monitor: 'user',
	*		  judgment: { score: 80 },
*			  info:
*			   { method: 'GET',
*			     created: 1490285296,
*			     score: 0,
*			     get: [],
*			     post: [],
	*		     server:
	*		      { 'server.USER': 'vagrant',
	*		        'server.HOME': '/home/vagrant',
	*		        'server.HTTP_UPGRADE_INSECURE_REQUESTS': '1',
	*		        'server.HTTP_CONNECTION': 'keep-alive',
	*		        'server.HTTP_ACCEPT_ENCODING': 'gzip, deflate',
	*		        'server.HTTP_ACCEPT_LANGUAGE': 'en-US,en;q=0.5',
	*		        'server.HTTP_ACCEPT': 'text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8',
		*	        'server.HTTP_USER_AGENT': 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0',
	*		        'server.HTTP_HOST': 'php.flash.app',
		*	        'server.REDIRECT_STATUS': '200',
		*	        'server.SERVER_NAME': 'php.flash.app',
		*	        'server.SERVER_PORT': '80',
	*		        'server.SERVER_ADDR': '192.168.100.10',
		*	        'server.REMOTE_PORT': '37350',
		*	        'server.REMOTE_ADDR': '192.168.100.1',
		*	        'server.SERVER_SOFTWARE': 'nginx/1.11.8',
		*	        'server.GATEWAY_INTERFACE': 'CGI/1.1',
		*	        'server.SERVER_PROTOCOL': 'HTTP/1.1',
		*	        'server.DOCUMENT_ROOT': '/home/vagrant/Code/shieldfy/flash/php-sdk',
			*        'server.DOCUMENT_URI': '/index.php',
		*	        'server.REQUEST_URI': '/',
		*	        'server.SCRIPT_NAME': '/index.php',
		*	        'server.SCRIPT_FILENAME': '/home/vagrant/Code/shieldfy/flash/php-sdk/index.php',
		*	        'server.CONTENT_LENGTH': '',
		*	        'server.CONTENT_TYPE': '',
		*	        'server.REQUEST_METHOD': 'GET',
			*        'server.QUERY_STRING': '',
		*	        'server.FCGI_ROLE': 'RESPONDER',
		*	        'server.PHP_SELF': '/index.php',
		*	        'server.REQUEST_TIME_FLOAT': 1490285295.351216,
			*        'server.REQUEST_TIME': 1490285295 },
		*	     cookies: [],
		*	     files: [] },
		*	  history: [] }
*
		 */

		//mark session as synced
		$this->session->markAsSynced();

		if($judgment['score'] >= self::HIGH ){
			if($this->config['action'] === 'block'){
				$this->respond()->block($incidentId);
			}
			return;
		}
		/*
		if($judgment['score'] >= self::MEDIUM){
			//report
			echo 'R <br />';
			echo $this->name.'<br />';
			print_r($judgment);
		//	file_put_contents(__dir__.'/log.txt',$this->name."\n".print_r($judgment,1));
			return;
		}

		if($judgment['score'] >= self::LOW){
			//report
			echo 'R <br />';
			echo $this->name.'<br />';
			print_r($judgment);
			//file_put_contents('./log.txt',$this->name."\n".print_r($judgment,1));
			//generate activityid
			//
			return;
		}
		*/
	}

	private function generateIncidentId($userId)
	{
		return $userId.time();
	}
}
