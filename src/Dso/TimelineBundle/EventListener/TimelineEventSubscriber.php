<?php

namespace Dso\TimelineBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Dso\TimelineBundle\Event\CreateTimelineEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TimelineEventSubscriber
 *
 * @package Dso\TimelineBundle\EventListener
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class TimelineEventSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager $em */
    private $em;

    public static function getSubscribedEvents()
    {
        return array(
            'create.timeline_event' => array('onCreateTimelineEvent', 1),
        );
    }

    /**
     * @param $em
     *
     * @return $this
     */
    public function setEm($em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * @param CreateTimelineEvent $event
     */
    public function onCreateTimelineEvent(CreateTimelineEvent $event)
    {
        $timelineEvent = $event->getTimelineEvent();
        $this->em->persist($timelineEvent);
        $this->em->flush();
    }
}
