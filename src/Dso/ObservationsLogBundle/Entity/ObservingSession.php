<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ObservingSession
 *
 * @ORM\Entity
 * @ORM\Table(name="observing_sessions")
 *
 * @package Dso\ObservationsLogBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class ObservingSession
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $period;

    //TODO: Think more for this one
    public $objects;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $equipment;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $location;

    /**
     * @param mixed $equipment
     *
     * @return ObservingSession
     */
    public function setEquipment($equipment)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @param mixed $id
     *
     * @return ObservingSession
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $location
     *
     * @return ObservingSession
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $period
     *
     * @return ObservingSession
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }
}
