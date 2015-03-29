<?php

namespace Dso\PlannerBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;

/**
 * Class PaginateDsosSubscriber
 *
 * @package Dso\PlannerBundle\Subscriber
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class PaginateDsosSubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event)
    {
        $event->count = count($event->target);
        $event->items = array_slice(
            $event->target,
            $event->getOffset(),
            $event->getLimit()
        );
        $event->stopPropagation();

    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 1/*increased priority to override any internal*/)
        );
    }
}
