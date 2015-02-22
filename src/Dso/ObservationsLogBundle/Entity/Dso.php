<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Dso
 *
 * @ORM\Entity
 * @ORM\Table(name="dsos")
 *
 * @package Dso\ObservationsLogBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class Dso
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $commonName;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $catalogName;

    /** @ORM\Column(type="string", nullable=TRUE) */
    public $catalogNameExtra;

    /**
     * @param mixed $catalogName
     *
     * @return Dso
     */
    public function setCatalogName($catalogName)
    {
        $this->catalogName = $catalogName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCatalogName()
    {
        return $this->catalogName;
    }

    /**
     * @param mixed $catalogNameExtra
     *
     * @return Dso
     */
    public function setCatalogNameExtra($catalogNameExtra)
    {
        $this->catalogNameExtra = $catalogNameExtra;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCatalogNameExtra()
    {
        return $this->catalogNameExtra;
    }

    /**
     * @param mixed $commonName
     *
     * @return Dso
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
     * @param mixed $id
     *
     * @return Dso
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
}
