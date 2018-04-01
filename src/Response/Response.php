<?php
namespace Shieldfy\Response;

use Shieldfy\Response\Respond;

trait Response
{
    public function respond()
    {
        $protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        $respond = new Respond($protocol);
        if ($this->config['blockPage']) {
            $respond->setBlockPage($this->config['blockPage']);
        }
        return $respond;
    }
}
