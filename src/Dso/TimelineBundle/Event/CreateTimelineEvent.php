<?php

namespace Dso\TimelineBundle\Event;

use Dso\TimelineBundle\Entity\TimelineEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CreateTimelineEvent
 *
 * @package Dso\PlannerBundle\Event
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class CreateTimelineEvent extends Event
{
    const CREATE_TIMELINE_EVENT = 'create.timeline_event';

    protected $timelineEvent;

    public function __construct(array $eventDetails)
    {
        $defaultEventDetails = array(
            'name' => '',
            'userId' => '',
            'obsListId' => '',
        );
        $safeEventDetails = array_merge($defaultEventDetails, $eventDetails);
        $timelineEvent = new TimelineEvent();
        $timelineEvent
            ->setName($safeEventDetails['name'])
            ->setUserId($safeEventDetails['userId'])
            ->setObsListId($safeEventDetails['obsListId']);
        $this->timelineEvent = $timelineEvent;
    }

    /**
     * @return TimelineEvent
     */
    public function getTimelineEvent()
    {
        return $this->timelineEvent;
    }
}
