<?php

namespace Dso\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ObservingSite
 *
 * @ORM\Entity
 * @ORM\Table(name="observing_sites")
 *
 * @package Dso\UserBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class ObservingSite
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $latitude;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $longitude;

    /**
     * @ORM\Column(name="time_zone", type="string", nullable=TRUE)
     */
    protected $timeZone;

    /**
     * @ORM\Column(name="user_id", type="integer", nullable=TRUE)
     */
    protected $userId;

    /**
     * Used to save the planned session time. TODO: Refactor this!
     *
     * @ORM\Column(name="date_time", type="string", nullable=TRUE)
     */
    protected $dateTime;

    /**
     * @param mixed $dateTime
     *
     * @return ObservingSite
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param mixed $userId
     *
     * @return ObservingSite
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $id
     *
     * @return ObservingSite
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
     * @param mixed $name
     *
     * @return ObservingSite
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $latitude
     *
     * @return ObservingSite
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $longitude
     *
     * @return ObservingSite
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $timeZone
     *
     * @return ObservingSite
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }
}
