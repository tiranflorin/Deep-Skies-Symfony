<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ObsList
 *
 * @ORM\Entity
 * @ORM\Table(name="obs_lists")
 *
 * @package Dso\ObservationsLogBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class ObsList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="integer", name="user_id") */
    protected $user_id;

    /** @ORM\Column(type="string", nullable=FALSE) */
    protected $name;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $period;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $equipment;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $conditions;

    /**
     * @ORM\ManyToOne(targetEntity="Object", inversedBy="obsLists")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     */
    private $dsoObject;

    protected $dsos;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param mixed $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return mixed
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @param mixed $equipment
     */
    public function setEquipment($equipment)
    {
        $this->equipment = $equipment;
    }

    /**
     * @return mixed
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param mixed $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     *
     * @return ObsList
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDsoObject()
    {
        return $this->dsoObject;
    }
    /**
     * @param mixed $dsoObject
     *
     * @return ObsList
     */
    public function setDsoObject($dsoObject)
    {
        $this->dsoObject = $dsoObject;

        return $this;
    }


    /**
     * @return array
     */
    public function getDsos()
    {
        return $this->dsos;
    }

    /**
     * @param array $dsos
     *
     * @return ObsList
     */
    public function setDsos($dsos)
    {
        $this->dsos = $dsos;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
