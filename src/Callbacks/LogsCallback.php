<?php
namespace Shieldfy\Callbacks;
use Shieldfy\Callbacks\CallbackInterface;
use Shieldfy\Shieldfy;
class LogsCallback implements CallbackInterface
{
    public static function handle(){
        //verify callback 
        $path = realpath(Shieldfy::getRootDir().'/../log');
        $data = [];
        $contents = scandir($path);
        foreach($contents as $file){
        	if($file == '.' || $file == '..' || $file == '.gitignore') continue;
        	$data[$file] = file_get_contents($path.'/'.$file);
        	@unlink($path.'/'.$file);
        }
        echo json_encode(['status'=>'success','message'=>$data]);
    }
}
