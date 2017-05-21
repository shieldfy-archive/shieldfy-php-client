<?php
namespace Shieldfy\Callbacks;

use Shieldfy\Callbacks\Callback;
use Shieldfy\Response\Response;

class LogsCallback extends Callback
{
    use Response;
    public function handle()
    {
        $path = realpath($this->config['rootDir'].'/../log');
        
        $data = [];
        $contents = scandir($path);
        foreach ($contents as $file) {
            if ($file == '.' || $file == '..' || $file == '.gitignore') {
                continue;
            }
            $filepath = $path.'/'.$file;
            if (is_readable($filepath)) {
                $data[$file] = file_get_contents($filepath);
                @unlink($filepath);
            }
        }

        //clean cache if any
        $cache = $this->cache;
        if(method_exists($cache,'clean')){
            $cache->clean(14400); //extend age to 4 hours to make sure nothing go wrong
        }

        $this->respond()->json($data);
    }
}
