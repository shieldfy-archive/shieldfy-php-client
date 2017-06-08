<?php
namespace Shieldfy\Response;

use Shieldfy\Response\Respond;

trait Response
{
    public function respond()
    {
        $protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        return new Respond($protocol);
    }
}
