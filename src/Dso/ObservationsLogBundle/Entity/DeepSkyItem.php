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
     * @ORM\Column(name="other_name", type="string", length=255, nullable=false)
     */
    private $otherName;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="constellation", type="string", length=255, nullable=false)
     */
    private $constellation;

    /**
     * @var string
     *
     * @ORM\Column(name="ra", type="string", length=255, nullable=false)
     */
    private $ra;

    /**
     * @var string
     *
     * @ORM\Column(name="dec", type="string", length=255, nullable=false)
     */
    private $dec;

    /**
     * @var string
     *
     * @ORM\Column(name="ra_float", type="string", length=30, nullable=false)
     */
    private $raFloat;

    /**
     * @var string
     *
     * @ORM\Column(name="dec_float", type="string", length=30, nullable=false)
     */
    private $decFloat;

    /**
     * @var string
     *
     * @ORM\Column(name="mag", type="decimal", precision=3, scale=1, nullable=false)
     */
    private $mag;

    /**
     * @var string
     *
     * @ORM\Column(name="subr", type="string", length=255, nullable=false)
     */
    private $subr;

    /**
     * @var string
     *
     * @ORM\Column(name="u2k", type="string", length=255, nullable=false)
     */
    private $u2k;

    /**
     * @var string
     *
     * @ORM\Column(name="ti", type="string", length=255, nullable=false)
     */
    private $ti;

    /**
     * @var string
     *
     * @ORM\Column(name="size_max", type="string", length=255, nullable=false)
     */
    private $sizeMax;

    /**
     * @var string
     *
     * @ORM\Column(name="size_min", type="string", length=255, nullable=false)
     */
    private $sizeMin;

    /**
     * @var string
     *
     * @ORM\Column(name="pa", type="string", length=255, nullable=false)
     */
    private $pa;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=false)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="nsts", type="string", length=255, nullable=false)
     */
    private $nsts;

    /**
     * @var string
     *
     * @ORM\Column(name="brstr", type="string", length=255, nullable=false)
     */
    private $brstr;

    /**
     * @var string
     *
     * @ORM\Column(name="bchm", type="string", length=255, nullable=false)
     */
    private $bchm;

    /**
     * @var string
     *
     * @ORM\Column(name="ngc_description", type="string", length=255, nullable=false)
     */
    private $ngcDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=false)
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
     * Set ra
     *
     * @param string $ra
     * @return DeepSkyItem
     */
    public function setRa($ra)
    {
        $this->ra = $ra;

        return $this;
    }

    /**
     * Get ra
     *
     * @return string 
     */
    public function getRa()
    {
        return $this->ra;
    }

    /**
     * Set dec
     *
     * @param string $dec
     * @return DeepSkyItem
     */
    public function setDec($dec)
    {
        $this->dec = $dec;

        return $this;
    }

    /**
     * Get dec
     *
     * @return string 
     */
    public function getDec()
    {
        return $this->dec;
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
     * Set subr
     *
     * @param string $subr
     * @return DeepSkyItem
     */
    public function setSubr($subr)
    {
        $this->subr = $subr;

        return $this;
    }

    /**
     * Get subr
     *
     * @return string 
     */
    public function getSubr()
    {
        return $this->subr;
    }

    /**
     * Set u2k
     *
     * @param string $u2k
     * @return DeepSkyItem
     */
    public function setU2k($u2k)
    {
        $this->u2k = $u2k;

        return $this;
    }

    /**
     * Get u2k
     *
     * @return string 
     */
    public function getU2k()
    {
        return $this->u2k;
    }

    /**
     * Set ti
     *
     * @param string $ti
     * @return DeepSkyItem
     */
    public function setTi($ti)
    {
        $this->ti = $ti;

        return $this;
    }

    /**
     * Get ti
     *
     * @return string 
     */
    public function getTi()
    {
        return $this->ti;
    }

    /**
     * Set sizeMax
     *
     * @param string $sizeMax
     * @return DeepSkyItem
     */
    public function setSizeMax($sizeMax)
    {
        $this->sizeMax = $sizeMax;

        return $this;
    }

    /**
     * Get sizeMax
     *
     * @return string 
     */
    public function getSizeMax()
    {
        return $this->sizeMax;
    }

    /**
     * Set sizeMin
     *
     * @param string $sizeMin
     * @return DeepSkyItem
     */
    public function setSizeMin($sizeMin)
    {
        $this->sizeMin = $sizeMin;

        return $this;
    }

    /**
     * Get sizeMin
     *
     * @return string 
     */
    public function getSizeMin()
    {
        return $this->sizeMin;
    }

    /**
     * Set pa
     *
     * @param string $pa
     * @return DeepSkyItem
     */
    public function setPa($pa)
    {
        $this->pa = $pa;

        return $this;
    }

    /**
     * Get pa
     *
     * @return string 
     */
    public function getPa()
    {
        return $this->pa;
    }

    /**
     * Set class
     *
     * @param string $class
     * @return DeepSkyItem
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set nsts
     *
     * @param string $nsts
     * @return DeepSkyItem
     */
    public function setNsts($nsts)
    {
        $this->nsts = $nsts;

        return $this;
    }

    /**
     * Get nsts
     *
     * @return string 
     */
    public function getNsts()
    {
        return $this->nsts;
    }

    /**
     * Set brstr
     *
     * @param string $brstr
     * @return DeepSkyItem
     */
    public function setBrstr($brstr)
    {
        $this->brstr = $brstr;

        return $this;
    }

    /**
     * Get brstr
     *
     * @return string 
     */
    public function getBrstr()
    {
        return $this->brstr;
    }

    /**
     * Set bchm
     *
     * @param string $bchm
     * @return DeepSkyItem
     */
    public function setBchm($bchm)
    {
        $this->bchm = $bchm;

        return $this;
    }

    /**
     * Get bchm
     *
     * @return string 
     */
    public function getBchm()
    {
        return $this->bchm;
    }

    /**
     * Set ngcDescription
     *
     * @param string $ngcDescription
     * @return DeepSkyItem
     */
    public function setNgcDescription($ngcDescription)
    {
        $this->ngcDescription = $ngcDescription;

        return $this;
    }

    /**
     * Get ngcDescription
     *
     * @return string 
     */
    public function getNgcDescription()
    {
        return $this->ngcDescription;
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
