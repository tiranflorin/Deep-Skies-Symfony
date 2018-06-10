<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Acl\Exception\Exception;

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

    /** @ORM\Column(type="integer", name="location_id") */
    protected $locationId;

    /** @ORM\Column(type="string", nullable=FALSE) */
    protected $name;

    /** @ORM\Column(type="datetime") */
    protected $start;

    /** @ORM\Column(type="datetime") */
    protected $end;

    /** @ORM\Column(type="text", nullable=TRUE) */
    protected $description;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $equipment;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $conditions;

    /** @ORM\Column(type="string", nullable=TRUE, name="visibility_level") */
    protected $visibilityLevel;

    /**
     * @ORM\ManyToOne(targetEntity="DeepSkyItem", inversedBy="obsLists")
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
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param mixed $locationId
     *
     * @return ObsList
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;

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

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return ObsList
     */
    public function setStart($start)
    {
        if (!($start instanceof \DateTime)) {
            try {
                $start = new \DateTime($start);
            } catch (Exception $e) {
                $start = new \DateTime('now', new \DateTimeZone('UTC'));
            }
        }

        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return ObsList
     */
    public function setEnd($end)
    {
        if (!($end instanceof \DateTime)) {
            try {
                $end = new \DateTime($end);
            } catch (Exception $e) {
                $end = new \DateTime('now', new \DateTimeZone('UTC'));
            }
        }

        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return ObsList
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getVisibilityLevel()
    {
        return $this->visibilityLevel;
    }

    /**
     * @param string $visibilityLevel
     *
     * @return ObsList
     */
    public function setVisibilityLevel($visibilityLevel)
    {
        $this->visibilityLevel = $visibilityLevel;

        return $this;
    }
}
