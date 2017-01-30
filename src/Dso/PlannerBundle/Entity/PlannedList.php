<?php

namespace Dso\PlannerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PlannedList
 *
 * @ORM\Entity
 * @ORM\Table(name="planned_lists")
 *
 * @package Dso\PlannerBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class PlannedList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="integer", name="user_id") */
    protected $userId;

    /** @ORM\Column(type="string", nullable=FALSE) */
    protected $name;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     *
     * @return PlannedList
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

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
     * @param mixed $name
     *
     * @return PlannedList
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
