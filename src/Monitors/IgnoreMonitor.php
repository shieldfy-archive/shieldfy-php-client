<?php
namespace Shieldfy\Monitors;

use Shieldfy\Config;

class IgnoreMonitor
{
    private $data;

    public function __construct($monitor)
    {
        $config = new Config;
        $ignoreFile = $config['paths']['data'].'/ignore.json';
        if (!file_exists($ignoreFile)) {
            $this->data = [];
            return;
        }
        $data = file_get_contents($ignoreFile);
        $data = json_decode($data, 1);
        $this->data = $data[$monitor];
    }

    public function filter($key, $value)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return $value;
    }
}
