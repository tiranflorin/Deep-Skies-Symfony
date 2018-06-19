<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * DeepSkyItem
 *
 * @ORM\Table(name="object")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Dso\ObservationsLogBundle\Entity\DsoObjectRepository")
 */
class DeepSkyItem
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="cat1", type="string", length=255, nullable=true)
     */
    private $cat1;

    /**
     * @var string
     *
     * @ORM\Column(name="id1", type="string", length=255, nullable=true)
     */
    private $id1;

    /**
     * @var string
     *
     * @ORM\Column(name="cat2", type="string", length=255, nullable=true)
     */
    private $cat2;

    /**
     * @var string
     *
     * @ORM\Column(name="id2", type="string", length=255, nullable=true)
     */
    private $id2;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="const", type="string", length=255, nullable=true)
     */
    private $constellation;

    /**
     * @var string
     *
     * @ORM\Column(name="rahour", type="string", length=30, nullable=true)
     */
    private $raFloat;

    /**
     * @var string
     *
     * @ORM\Column(name="decdeg", type="string", length=30, nullable=true)
     */
    private $decFloat;

    /**
     * @var string
     *
     * @ORM\Column(name="rarad", type="string", length=30, nullable=true)
     */
    private $raRad;

    /**
     * @var string
     *
     * @ORM\Column(name="decrad", type="string", length=30, nullable=true)
     */
    private $decRad;

    /**
     * @var string
     *
     * @ORM\Column(name="databaseid", type="string", length=30, nullable=true)
     */
    private $databaseId;

    /**
     * @var string
     *
     * @ORM\Column(name="semimajor", type="string", length=30, nullable=true)
     */
    private $semiMajor;

    /**
     * @var string
     *
     * @ORM\Column(name="semiminor", type="string", length=30, nullable=true)
     */
    private $semiMinor;

    /**
     * @var string
     *
     * @ORM\Column(name="semimajorangle", type="string", length=30, nullable=true)
     */
    private $semiMajorAngle;

    /**
     * @var string
     */
    private $objectSource;

    /**
     * @var string
     *
     * @ORM\Column(name="dupid", type="string", length=30, nullable=true)
     */
    private $dupId;

    /**
     * @var string
     *
     * @ORM\Column(name="dupcat", type="string", length=30, nullable=true)
     */
    private $dupCat;

    /**
     * @var string
     *
     * @ORM\Column(name="display_mag", type="string", length=30, nullable=true)
     */
    private $displayMag;

    /**
     * @var string
     *
     * @ORM\Column(name="mag", type="decimal", precision=3, scale=1, nullable=true)
     */
    private $mag;

    /**
     * @var string
     *
     * @ORM\Column(name="other_names", type="string", length=255, nullable=true)
     */
    private $otherName;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ObsList", mappedBy="dsoObject")
     */
    private $obsLists;

    public function __construct() {
        $this->obsLists = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return DeepSkyItem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set otherName
     *
     * @param string $otherName
     * @return DeepSkyItem
     */
    public function setOtherName($otherName)
    {
        $this->otherName = $otherName;

        return $this;
    }

    /**
     * Get otherName
     *
     * @return string 
     */
    public function getOtherName()
    {
        return $this->otherName;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return DeepSkyItem
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set constellation
     *
     * @param string $constellation
     * @return DeepSkyItem
     */
    public function setConstellation($constellation)
    {
        $this->constellation = $constellation;

        return $this;
    }

    /**
     * Get constellation
     *
     * @return string 
     */
    public function getConstellation()
    {
        return $this->constellation;
    }

    /**
     * Set raFloat
     *
     * @param string $raFloat
     * @return DeepSkyItem
     */
    public function setRaFloat($raFloat)
    {
        $this->raFloat = $raFloat;

        return $this;
    }

    /**
     * Get raFloat
     *
     * @return string 
     */
    public function getRaFloat()
    {
        return $this->raFloat;
    }

    /**
     * Set decFloat
     *
     * @param string $decFloat
     * @return DeepSkyItem
     */
    public function setDecFloat($decFloat)
    {
        $this->decFloat = $decFloat;

        return $this;
    }

    /**
     * Get decFloat
     *
     * @return string 
     */
    public function getDecFloat()
    {
        return $this->decFloat;
    }

    /**
     * Set mag
     *
     * @param string $mag
     * @return DeepSkyItem
     */
    public function setMag($mag)
    {
        $this->mag = $mag;

        return $this;
    }

    /**
     * Get mag
     *
     * @return string 
     */
    public function getMag()
    {
        return $this->mag;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return DeepSkyItem
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return string
     */
    public function getCat1()
    {
        return $this->cat1;
    }

    /**
     * @param string $cat1
     * @return DeepSkyItem
     */
    public function setCat1($cat1)
    {
        $this->cat1 = $cat1;

        return $this;
    }

    /**
     * @return string
     */
    public function getId1()
    {
        return $this->id1;
    }

    /**
     * @param string $id1
     * @return DeepSkyItem
     */
    public function setId1($id1)
    {
        $this->id1 = $id1;

        return $this;
    }

    /**
     * @return string
     */
    public function getCat2()
    {
        return $this->cat2;
    }

    /**
     * @param string $cat2
     * @return DeepSkyItem
     */
    public function setCat2($cat2)
    {
        $this->cat2 = $cat2;

        return $this;
    }

    /**
     * @return string
     */
    public function getId2()
    {
        return $this->id2;
    }

    /**
     * @param string $id2
     * @return DeepSkyItem
     */
    public function setId2($id2)
    {
        $this->id2 = $id2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getObsLists()
    {
        return $this->obsLists;
    }

    /**
     * @param mixed $obsLists
     *
     * @return DeepSkyItem
     */
    public function setObsLists($obsLists)
    {
        $this->obsLists = $obsLists;

        return $this;
    }
}
