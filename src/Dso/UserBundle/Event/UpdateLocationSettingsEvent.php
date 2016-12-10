<?php

namespace Dso\UserBundle\Event;

use Dso\UserBundle\Entity\ObservingSite;
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

    protected $locationDetails;

    public function __construct(ObservingSite $locationDetails) {
        $this->locationDetails = $locationDetails;
    }

    /**
     * @return ObservingSite
     */
    public function getLocationDetails()
    {
        return $this->locationDetails;
    }
}
