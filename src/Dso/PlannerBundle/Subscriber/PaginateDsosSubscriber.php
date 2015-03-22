<?php

namespace Dso\PlannerBundle\Subscriber;

use Dso\PlannerBundle\Services\SQL\MySqlService;
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
    /** @var  MySqlService */
    private $persistence;

    public function __construct($persistence)
    {
        $this->persistence = $persistence;
    }

    public function items(ItemsEvent $event)
    {
        $results = $this->persistence->executeSelectQuery($event->target);
        $event->count = count($results);
        $event->items = array_slice(
            $results,
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
