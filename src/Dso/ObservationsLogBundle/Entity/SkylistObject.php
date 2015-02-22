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
    public $id;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $catalogNumberNgc;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $catalogNumberMessier;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $catalogNumberIc;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $commonName;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $dateObserved;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $userName;

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
}
