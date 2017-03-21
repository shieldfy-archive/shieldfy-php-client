<?php
namespace Shieldfy\Callbacks;
use Shieldfy\Config;
abstract class Callback
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    abstract function handle();
}
