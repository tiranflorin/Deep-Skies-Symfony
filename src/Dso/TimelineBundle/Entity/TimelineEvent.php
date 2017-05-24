<?php

namespace Dso\TimelineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Class TimelineEvent
 *
 * @ORM\Entity
 * @ORM\Table(name="timeline_events")
 * @ORM\HasLifecycleCallbacks
 *
 * @package Dso\TimelineBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class TimelineEvent
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /** @ORM\Column(type="integer", name="user_id") */
    protected $userId;

    /** @ORM\Column(type="integer", name="obs_list_id", nullable=TRUE) */
    protected $obsListId;

    /** @ORM\Column(type="datetime", name="added_on") */
    protected $addedOn;

    /**
     * @ORM\PrePersist
     *
     * @return TimelineEvent
     */
    public function populateAddedOn()
    {
        if (!($this->addedOn instanceof \DateTime)) {
            $this->addedOn = new \DateTime('now', new \DateTimeZone('UTC'));
        }

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
     * @return TimelineEvent
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getObsListId()
    {
        return $this->obsListId;
    }

    /**
     * @param mixed $obsListId
     *
     * @return TimelineEvent
     */
    public function setObsListId($obsListId)
    {
        $this->obsListId = $obsListId;

        return $this;
    }

    /**
     * Set start
     *
     * @param \DateTime $addedOn
     * @return TimelineEvent
     */
    public function setAddedOn($addedOn)
    {
        if (!($addedOn instanceof \DateTime)) {
            try {
                $addedOn = new \DateTime($addedOn);
            } catch (Exception $e) {
                $addedOn = new \DateTime('now', new \DateTimeZone('UTC'));
            }
        }

        $this->addedOn = $addedOn;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return TimelineEvent
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }



    public function __toString()
    {
        return (string)$this->id;
    }
}
