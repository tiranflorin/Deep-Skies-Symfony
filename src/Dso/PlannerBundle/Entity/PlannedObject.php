<?php

namespace Dso\PlannerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PlannedObject
 *
 * @ORM\Entity
 * @ORM\Table(name="planned_objects")
 * @ORM\HasLifecycleCallbacks()
 *
 * @package Dso\PlannerBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class PlannedObject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="integer", name="obj_id") */
    protected $objId;

    /** @ORM\Column(type="integer", name="user_id") */
    protected $userId;

    /** @ORM\Column(type="integer", name="list_id") */
    protected $listId;

    /** @ORM\Column(type="text", name="notes") */
    protected $notes;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set objId
     *
     * @param integer $objId
     * @return PlannedObject
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId
     *
     * @return integer
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return PlannedObject
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set listId
     *
     * @param integer $listId
     * @return PlannedObject
     */
    public function setListId($listId)
    {
        $this->listId = $listId;

        return $this;
    }

    /**
     * Get listId
     *
     * @return integer
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        if ($this->notes == 0) {
            $this->notes = null;
        }

        return $this->notes;
    }

    /**
     * @param mixed $notes
     * @return PlannedObject
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }
}
