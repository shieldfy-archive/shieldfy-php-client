<?php
namespace Shieldfy\Callbacks;
use Shieldfy\Callbacks\CallbackInterface;
use Shieldfy\Shieldfy;
class PingCallback implements CallbackInterface
{
    public static function handle(){
        //ping callback
        echo json_encode(['status'=>'success','message'=>Shieldfy::getApiVersion()]);
    }
}
