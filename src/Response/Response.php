<?php
namespace Shieldfy\Response;
use Shieldfy\Response\Respond;
trait Response
{
    public function respond()
    {
        return new Respond();
    }
}
