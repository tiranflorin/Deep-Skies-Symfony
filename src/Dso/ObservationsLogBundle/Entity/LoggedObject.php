<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class LoggedObject
 *
 * @ORM\Entity
 * @ORM\Table(name="logged_objects")
 * @ORM\HasLifecycleCallbacks()
 *
 * @package Dso\ObservationsLogBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class LoggedObject
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

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $comment;

    /** @ORM\Column(type="datetime") */
    protected $observedAt;

    /** @ORM\Column(type="datetime") */
    protected $createdAt;

    /**
     * @ORM\OneToOne(targetEntity="ObsList")
     * @ORM\JoinColumn(name="listId", referencedColumnName="id")
     **/
    private $obsList;

    // TODO add the same relationship to user_id and object_id

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

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
     * @return LoggedObject
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
     * @return LoggedObject
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
     * @return LoggedObject
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
     * Set comment
     *
     * @param string $comment
     * @return LoggedObject
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return LoggedObject
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set obsList
     *
     * @param \Dso\ObservationsLogBundle\Entity\ObsList $obsList
     * @return LoggedObject
     */
    public function setObsList(\Dso\ObservationsLogBundle\Entity\ObsList $obsList = null)
    {
        $this->obsList = $obsList;

        return $this;
    }

    /**
     * Get obsList
     *
     * @return \Dso\ObservationsLogBundle\Entity\ObsList
     */
    public function getObsList()
    {
        return $this->obsList;
    }

    /**
     * Get observedAt
     *
     * @return \DateTime
     */
    public function getObservedAt()
    {
        return $this->observedAt;
    }

    /**
     * Set observedAt
     *
     * @param mixed $observedAt
     * @return LoggedObject
     */
    public function setObservedAt($observedAt)
    {
        $this->observedAt = $observedAt;

        return $this;
    }
}
