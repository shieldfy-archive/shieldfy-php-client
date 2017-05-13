<?php
namespace Shieldfy\Callbacks;

use Shieldfy\Callbacks\Callback;
use Shieldfy\Response\Response;

class HealthCheckCallback extends Callback
{
    use Response;
    public function handle()
    {
        $this->respond()->json(['status'=>'success','version'=>$this->config['version']]);
    }
}
