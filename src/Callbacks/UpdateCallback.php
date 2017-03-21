<?php
namespace Shieldfy\Callbacks;
use Shieldfy\Callbacks\Callback;
use Shieldfy\Response\Response;
use Shieldfy\Updater;

class UpdateCallback extends Callback
{
    use Response;
    public function handle()
    {
        $install = (new Updater($this->config))->run();
        $this->respond()->json(['status'=>'success']);
    }
}
