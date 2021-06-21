<?php

namespace App\EventListener;

use FOS\HttpCache\ProxyClient\Varnish;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CacheInvalidationListener implements EventSubscriberInterface
{
    private Varnish $varnish;

    public function __construct(Varnish $varnish)
    {
        $this->varnish = $varnish;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['onKernelRequest']];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (\in_array($request->getMethod(), ['GET', 'HEAD'])) {
            return;
        }

        $this->varnish->purge('/');
        $this->varnish->flush();
    }
}
