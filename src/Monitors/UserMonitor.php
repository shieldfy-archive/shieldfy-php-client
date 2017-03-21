<?php
namespace Shieldfy\Monitors;

class UserMonitor extends MonitorBase
{

	protected $name = "user";

	/**
	 * Monitor user score / reason
	 */
	public function run()
	{
		$user = $this->collectors['user'];
		//report user only on first Session
		if(!$this->session->isNewVisit()){
			return;
		}
		$score = $user->getScore();
		$this->handle([
			'score' => $score
		]);
	}

}
