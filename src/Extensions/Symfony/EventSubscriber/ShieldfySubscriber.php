<?php

namespace Shieldfy\Extensions\Symfony\EventSubscriber;

use Shieldfy\Extensions\Symfony\ShieldfySymfonyControllerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ShieldfySubscriber implements EventSubscriberInterface
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if ($controller[0] instanceof ShieldfySymfonyControllerInterface) {
            $class = new \ReflectionClass($controller[0]);
            $property = $class->getMethod("getDoctrine");
            $property->setAccessible(true);
            $doc = $property->invoke($controller[0]);
            $em = $doc->getManager();
            $logger = new \Shieldfy\Extensions\Symfony\Logger($em->getConnection());
            $em->getConnection()->getConfiguration()->setSQLLogger($logger);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }
}
