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

        $score = $user->getScore();
        if($score){
            $this->sendToJail($this->parseScore($score), $charge  = []);    
        }
        
    }


    
}
