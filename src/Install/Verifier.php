<?php
namespace Shieldfy\Install;

use Shieldfy\Config;
use Shieldfy\Response\Notification;

/**
 * Verify installation 
 * usage : 
 * $verifier = (new Verifier($config, $request))->whoIsCalling();
 * $verifier->success(); //installation successful
 * $verifier->error($message); //installation has errors
 * $verifier->check(); //check the installation after success (just to be clear everything is good)
 */
class Verifier
{
    protected $config;
    protected $request;
    protected $silent = true;
    protected $notification = null;

    public function __construct(Config $config, $request)
    {
        $this->config = $config;
        $this->request = $request; 
    }

    public function whoIsCalling()
    {
        if (isset($this->request->get['shieldfy']) && $this->request->get['shieldfy'] == 'verified'){

            //shieldfy is calling , so lets silent is false;
            $this->silent = false;
            $this->notification = new Notification;

            $hash = isset($this->request->get['hash']) ? $this->request->get['hash'] : '';
            $appHash = hash_hmac('sha256', $this->config['app_secret'], $this->config['app_key']);
            if ($hash !== $appHash) {
                $this->error('There installation keys is incorrect');
                exit; // exit because its a special request sent by shieldfy only
            }
                        
        }        
        return $this;
    }

    public function check()
    {   
        if($this->silent) return;

        //It passes all the installation process & keys are correct so all code
        $this->success();
    }
    

    public function success()
    {
        $this->show('success','The installation process is successful');
    }

    public function error($message)
    {
       $this->show('error',$message);
    }

    private function show($type, $message)
    {
        if($this->silent) return;

        header('X-shieldfy-verification: verify');
        header('X-shieldfy-verification-status: ' . $type);
        header('X-shieldfy-verification-message: ' . $message);

        if($type == 'success') $this->notification->success($message);
        if($type == 'error') $this->notification->error($message);
    }

    
    
}
