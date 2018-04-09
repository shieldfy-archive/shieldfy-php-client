<?php
namespace Shieldfy\Extensions\Symfony\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        echo "I am here bro :)";
    }
}
