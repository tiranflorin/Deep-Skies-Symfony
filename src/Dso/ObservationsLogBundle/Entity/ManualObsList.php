<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Class ManualObsList
 *
 * @ORM\Entity
 * @ORM\Table(name="manual_obs_lists")
 *
 * @package Dso\ObservationsLogBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class ManualObsList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="string", nullable=TRUE) */ //TODO!!!
    protected $dsos;

    /** @ORM\Column(type="string", nullable=FALSE) */
    protected $name;

    /** @ORM\Column(type="string", nullable=FALSE) */
    protected $period;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $equipment;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $conditions;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return ManualObsList
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return array
     */
    public function getDsos() {
        return $this->dsos;
    }

    /**
     * @param array $dsos
     *
     * @return ManualObsList
     */
    public function setDsos(array $dsos) {
        $this->dsos = $dsos;

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
     * @return ManualObsList
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     *
     * @return ManualObsList
     */
    public function setPeriod($period)
    {
        $this->period = $period;

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
     * @param mixed $equipment
     *
     * @return ManualObsList
     */
    public function setEquipment($equipment)
    {
        $this->equipment = $equipment;

        return $this;
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
     *
     * @return ManualObsList
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }
}
