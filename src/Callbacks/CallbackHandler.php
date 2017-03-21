<?php
namespace Shieldfy\Callbacks;
use Shieldfy\Config;
use Shieldfy\Collectors\RequestCollector;
use Shieldfy\Response\Response;
class CallbackHandler
{
    use Response;
    protected $request;
    protected $callbacks = [
        'health' => \Shieldfy\Callbacks\HealthCheckCallback::class,
        'update' => \Shieldfy\Callbacks\UpdateCallback::class,
        'logs'   => \Shieldfy\Callbacks\LogsCallback::class,
    ];

    public function __construct(RequestCollector $request,Config $config)
    {
        $this->request = $request;
        $this->config  = $config;
    }

    public function catchCallback()
    {
        if(isset($this->request->server['HTTP_X_SHIELDFY_CALLBACK']))
        {
            //callback needs catch
            $callback = $this->request->server['HTTP_X_SHIELDFY_CALLBACK'];
            if(!isset($this->callbacks[$callback])){
                //throw exception
                $this->respond()->json(['status'=>'error'],404,'Callback not found');
            }

            $callbackClass = $this->callbacks[$callback];
            $callback = new $callbackClass($this->config);
            $callback->handle();
        }
    }
}
