<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SkylistObject
 *
 * @ORM\Entity
 * @ORM\Table(name="skylist_object")
 *
 * @package Dso\ObservationsLogBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class SkylistObject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $catalogNumberNgc;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $catalogNumberMessier;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $catalogNumberIc;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $commonName;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $dateObserved;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $comment;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $userName;

    /** @ORM\Column(type="string", nullable=TRUE) */
    protected $observingSessionName;

    /**
     * @param mixed $catalogNumberIc
     */
    public function setCatalogNumberIc($catalogNumberIc)
    {
        $this->catalogNumberIc = $catalogNumberIc;
    }

    /**
     * @return mixed
     */
    public function getCatalogNumberIc()
    {
        return $this->catalogNumberIc;
    }

    /**
     * @param mixed $catalogNumberMessier
     *
     * @return SkylistObject
     */
    public function setCatalogNumberMessier($catalogNumberMessier)
    {
        $this->catalogNumberMessier = $catalogNumberMessier;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCatalogNumberMessier()
    {
        return $this->catalogNumberMessier;
    }

    /**
     * @param mixed $catalogNumberNgc
     *
     * @return SkylistObject
     */
    public function setCatalogNumberNgc($catalogNumberNgc)
    {
        $this->catalogNumberNgc = $catalogNumberNgc;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCatalogNumberNgc()
    {
        return $this->catalogNumberNgc;
    }

    /**
     * @param mixed $commonName
     *
     * @return SkylistObject
     */
    public function setCommonName($commonName)
    {
        $this->commonName = $commonName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommonName()
    {
        return $this->commonName;
    }

    /**
     * @param mixed $dateObserved
     *
     * @return SkylistObject
     */
    public function setDateObserved($dateObserved)
    {
        $this->dateObserved = $dateObserved;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateObserved()
    {
        return $this->dateObserved;
    }

    /**
     * @param mixed $userName
     *
     * @return SkylistObject
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param mixed $comment
     *
     * @return SkylistObject
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $observingSessionName
     *
     * @return SkylistObject
     */
    public function setObservingSessionName($observingSessionName)
    {
        $this->observingSessionName = $observingSessionName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getObservingSessionName()
    {
        return $this->observingSessionName;
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
}
