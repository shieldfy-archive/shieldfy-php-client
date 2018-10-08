<?php
namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Collectors\RequestCollector;
use Shieldfy\Response\Response;
use Shieldfy\Http\Dispatcher;

class CallbackHandler
{
    use Response;
    protected $request;
    protected $config;
    protected $dispatcher;

    protected $callbacks = [
        'health' => \Shieldfy\Callbacks\HealthCheckCallback::class,
        'update' => \Shieldfy\Callbacks\UpdateCallback::class,
        'vendor' => \Shieldfy\Callbacks\VendorCallback::class
    ];

    public function __construct(RequestCollector $request, Config $config, Dispatcher $dispatcher)
    {
        $this->request = $request;
        $this->config  = $config;
        $this->dispatcher = $dispatcher;
    }

    public function catchCallback()
    {
        if (!isset($this->request->server['HTTP_X_SHIELDFY_CALLBACK'])) {
            return; // No callback.
        }
        $callback = $this->request->server['HTTP_X_SHIELDFY_CALLBACK'];
        if (!isset($this->callbacks[$callback])) {
            $this->respond()->json(['status' => 'error'], 404, 'Callback not found');
        }

        if (!$this->verify()) {
            $this->respond()->json(['status' => 'error'], 401, 'Unauthorized callback');
        }

        $callbackClass = $this->callbacks[$callback];
        $callback = new $callbackClass($this->config, $this->dispatcher);
        $callback->handle();
    }

    /**
     * Verify call token
     * @return boolean result
     */
    private function verify()
    {
        if (!isset($this->request->server['HTTP_X_SHIELDFY_CALLBACK_TOKEN'])) {
            return false;
        }
        $token = $this->request->server['HTTP_X_SHIELDFY_CALLBACK_TOKEN'];
        $localToken = hash_hmac('sha256', $this->config['app_key'], $this->config['app_secret']);
        if ($localToken === $token) {
            return true;
        }
        return false;
    }
}
