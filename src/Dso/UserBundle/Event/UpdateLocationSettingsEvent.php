<?php

namespace Dso\UserBundle\Event;

use Dso\UserBundle\Entity\LocationDetails;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class UpdateUserLocationSettingsEvent
 *
 * @package Dso\PlannerBundle\Event
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class UpdateLocationSettingsEvent extends Event
{
    const UPDATE_LOCATION = 'update.location';
    const UPDATE_TIME = 'update.time';

    protected $locationDetails;

    public function __construct(LocationDetails $locationDetails) {
        $this->locationDetails = $locationDetails;
    }

    /**
     * @return LocationDetails
     */
    public function getLocationDetails()
    {
        return $this->locationDetails;
    }
}
