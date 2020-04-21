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
     * @var float
     *
     * @ORM\Column(name="rahour", type="float", precision=10, scale=0, nullable=true)
     */
    private $rahour;

    /**
     * @var float
     *
     * @ORM\Column(name="decdeg", type="float", precision=10, scale=0, nullable=true)
     */
    private $decdeg;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=20, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="const", type="string", length=20, nullable=true)
     */
    private $const;

    /**
     * @var float
     *
     * @ORM\Column(name="mag", type="float", precision=10, scale=0, nullable=true)
     */
    private $mag;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="rarad", type="float", precision=10, scale=0, nullable=true)
     */
    private $rarad;

    /**
     * @var float
     *
     * @ORM\Column(name="decrad", type="float", precision=10, scale=0, nullable=true)
     */
    private $decrad;

    /**
     * @var integer
     *
     * @ORM\Column(name="databaseid", type="integer", nullable=true)
     */
    private $databaseid;

    /**
     * @var float
     *
     * @ORM\Column(name="semimajor", type="float", precision=10, scale=0, nullable=true)
     */
    private $semimajor;

    /**
     * @var float
     *
     * @ORM\Column(name="semiminor", type="float", precision=10, scale=0, nullable=true)
     */
    private $semiminor;

    /**
     * @var float
     *
     * @ORM\Column(name="semimajorangle", type="float", precision=10, scale=0, nullable=true)
     */
    private $semimajorangle;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_source", type="integer", nullable=true)
     */
    private $objectSource;

    /**
     * @var string
     *
     * @ORM\Column(name="id1", type="string", length=25, nullable=true)
     */
    private $id1;

    /**
     * @var string
     *
     * @ORM\Column(name="cat1", type="string", length=25, nullable=true)
     */
    private $cat1;

    /**
     * @var string
     *
     * @ORM\Column(name="id2", type="string", length=25, nullable=true)
     */
    private $id2;

    /**
     * @var string
     *
     * @ORM\Column(name="cat2", type="string", length=25, nullable=true)
     */
    private $cat2;

    /**
     * @var string
     *
     * @ORM\Column(name="dupid", type="string", length=25, nullable=true)
     */
    private $dupid;

    /**
     * @var string
     *
     * @ORM\Column(name="dupcat", type="string", length=25, nullable=true)
     */
    private $dupcat;

    /**
     * @var float
     *
     * @ORM\Column(name="display_mag", type="float", precision=10, scale=0, nullable=true)
     */
    private $displayMag;

    /**
     * @var string
     *
     * @ORM\Column(name="other_names", type="text", nullable=true)
     */
    private $otherNames;

    /**
     * @var string
     *
     * @ORM\Column(name="nice_foto_target_at_focals", type="text", nullable=true)
     */
    private $niceFotoTargetAtFocals;

    /**
     * @var integer
     *
     * @ORM\Column(name="id3", type="integer", nullable=true)
     */
    private $id3;

    /**
     * @var string
     *
     * @ORM\Column(name="cat3", type="string", length=10, nullable=true)
     */
    private $cat3;

    /**
     * @var string
     *
     * @ORM\Column(name="arp_comment", type="text", nullable=true)
     */
    private $arpComment;

    /**
     * @var string
     *
     * @ORM\Column(name="arp_category", type="text", nullable=true)
     */
    private $arpCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="description_nosearch", type="text", nullable=true)
     */
    private $descriptionNosearch;

    /**
     * @var string
     *
     * @ORM\Column(name="searchable_but_dontdisplay", type="string", length=200, nullable=false)
     */
    private $searchableButDontdisplay;

    /**
     * @var integer
     *
     * @ORM\Column(name="id4", type="integer", nullable=false)
     */
    private $id4;

    /**
     * @var string
     *
     * @ORM\Column(name="cat4", type="string", length=10, nullable=false)
     */
    private $cat4;

    /**
     * @var integer
     *
     * @ORM\Column(name="id5", type="integer", nullable=true)
     */
    private $id5;

    /**
     * @var string
     *
     * @ORM\Column(name="cat5", type="string", length=10, nullable=false)
     */
    private $cat5;

    /**
     * @var integer
     *
     * @ORM\Column(name="boring_target", type="integer", nullable=false)
     */
    private $boringTarget;

    /**
     * @var integer
     *
     * @ORM\Column(name="deleted", type="integer", nullable=false)
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string", length=300, nullable=true)
     */
    private $notes;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @return float
     */
    public function getRahour()
    {
        return $this->rahour;
    }

    /**
     * @param float $rahour
     * @return DeepSkyItem
     */
    public function setRahour($rahour)
    {
        $this->rahour = $rahour;
        return $this;
    }

    /**
     * @return float
     */
    public function getDecdeg()
    {
        return $this->decdeg;
    }

    /**
     * @param float $decdeg
     * @return DeepSkyItem
     */
    public function setDecdeg($decdeg)
    {
        $this->decdeg = $decdeg;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return DeepSkyItem
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getConst()
    {
        return $this->const;
    }

    /**
     * @param string $const
     * @return DeepSkyItem
     */
    public function setConst($const)
    {
        $this->const = $const;
        return $this;
    }

    /**
     * @return float
     */
    public function getMag()
    {
        return $this->mag;
    }

    /**
     * @param float $mag
     * @return DeepSkyItem
     */
    public function setMag($mag)
    {
        $this->mag = $mag;
        return $this;
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
     * @return DeepSkyItem
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return float
     */
    public function getRarad()
    {
        return $this->rarad;
    }

    /**
     * @param float $rarad
     * @return DeepSkyItem
     */
    public function setRarad($rarad)
    {
        $this->rarad = $rarad;
        return $this;
    }

    /**
     * @return float
     */
    public function getDecrad()
    {
        return $this->decrad;
    }

    /**
     * @param float $decrad
     * @return DeepSkyItem
     */
    public function setDecrad($decrad)
    {
        $this->decrad = $decrad;
        return $this;
    }

    /**
     * @return int
     */
    public function getDatabaseid()
    {
        return $this->databaseid;
    }

    /**
     * @param int $databaseid
     * @return DeepSkyItem
     */
    public function setDatabaseid($databaseid)
    {
        $this->databaseid = $databaseid;
        return $this;
    }

    /**
     * @return float
     */
    public function getSemimajor()
    {
        return $this->semimajor;
    }

    /**
     * @param float $semimajor
     * @return DeepSkyItem
     */
    public function setSemimajor($semimajor)
    {
        $this->semimajor = $semimajor;
        return $this;
    }

    /**
     * @return float
     */
    public function getSemiminor()
    {
        return $this->semiminor;
    }

    /**
     * @param float $semiminor
     * @return DeepSkyItem
     */
    public function setSemiminor($semiminor)
    {
        $this->semiminor = $semiminor;
        return $this;
    }

    /**
     * @return float
     */
    public function getSemimajorangle()
    {
        return $this->semimajorangle;
    }

    /**
     * @param float $semimajorangle
     * @return DeepSkyItem
     */
    public function setSemimajorangle($semimajorangle)
    {
        $this->semimajorangle = $semimajorangle;
        return $this;
    }

    /**
     * @return int
     */
    public function getObjectSource()
    {
        return $this->objectSource;
    }

    /**
     * @param int $objectSource
     * @return DeepSkyItem
     */
    public function setObjectSource($objectSource)
    {
        $this->objectSource = $objectSource;
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
    public function getDupid()
    {
        return $this->dupid;
    }

    /**
     * @param string $dupid
     * @return DeepSkyItem
     */
    public function setDupid($dupid)
    {
        $this->dupid = $dupid;
        return $this;
    }

    /**
     * @return string
     */
    public function getDupcat()
    {
        return $this->dupcat;
    }

    /**
     * @param string $dupcat
     * @return DeepSkyItem
     */
    public function setDupcat($dupcat)
    {
        $this->dupcat = $dupcat;
        return $this;
    }

    /**
     * @return float
     */
    public function getDisplayMag()
    {
        return $this->displayMag;
    }

    /**
     * @param float $displayMag
     * @return DeepSkyItem
     */
    public function setDisplayMag($displayMag)
    {
        $this->displayMag = $displayMag;
        return $this;
    }

    /**
     * @return string
     */
    public function getOtherNames()
    {
        return $this->otherNames;
    }

    /**
     * @param string $otherNames
     * @return DeepSkyItem
     */
    public function setOtherNames($otherNames)
    {
        $this->otherNames = $otherNames;
        return $this;
    }

    /**
     * @return string
     */
    public function getNiceFotoTargetAtFocals()
    {
        return $this->niceFotoTargetAtFocals;
    }

    /**
     * @param string $niceFotoTargetAtFocals
     * @return DeepSkyItem
     */
    public function setNiceFotoTargetAtFocals($niceFotoTargetAtFocals)
    {
        $this->niceFotoTargetAtFocals = $niceFotoTargetAtFocals;
        return $this;
    }

    /**
     * @return int
     */
    public function getId3()
    {
        return $this->id3;
    }

    /**
     * @param int $id3
     * @return DeepSkyItem
     */
    public function setId3($id3)
    {
        $this->id3 = $id3;
        return $this;
    }

    /**
     * @return string
     */
    public function getCat3()
    {
        return $this->cat3;
    }

    /**
     * @param string $cat3
     * @return DeepSkyItem
     */
    public function setCat3($cat3)
    {
        $this->cat3 = $cat3;
        return $this;
    }

    /**
     * @return string
     */
    public function getArpComment()
    {
        return $this->arpComment;
    }

    /**
     * @param string $arpComment
     * @return DeepSkyItem
     */
    public function setArpComment($arpComment)
    {
        $this->arpComment = $arpComment;
        return $this;
    }

    /**
     * @return string
     */
    public function getArpCategory()
    {
        return $this->arpCategory;
    }

    /**
     * @param string $arpCategory
     * @return DeepSkyItem
     */
    public function setArpCategory($arpCategory)
    {
        $this->arpCategory = $arpCategory;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescriptionNosearch()
    {
        return $this->descriptionNosearch;
    }

    /**
     * @param string $descriptionNosearch
     * @return DeepSkyItem
     */
    public function setDescriptionNosearch($descriptionNosearch)
    {
        $this->descriptionNosearch = $descriptionNosearch;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchableButDontdisplay()
    {
        return $this->searchableButDontdisplay;
    }

    /**
     * @param string $searchableButDontdisplay
     * @return DeepSkyItem
     */
    public function setSearchableButDontdisplay($searchableButDontdisplay)
    {
        $this->searchableButDontdisplay = $searchableButDontdisplay;
        return $this;
    }

    /**
     * @return int
     */
    public function getId4()
    {
        return $this->id4;
    }

    /**
     * @param int $id4
     * @return DeepSkyItem
     */
    public function setId4($id4)
    {
        $this->id4 = $id4;
        return $this;
    }

    /**
     * @return string
     */
    public function getCat4()
    {
        return $this->cat4;
    }

    /**
     * @param string $cat4
     * @return DeepSkyItem
     */
    public function setCat4($cat4)
    {
        $this->cat4 = $cat4;
        return $this;
    }

    /**
     * @return int
     */
    public function getId5()
    {
        return $this->id5;
    }

    /**
     * @param int $id5
     * @return DeepSkyItem
     */
    public function setId5($id5)
    {
        $this->id5 = $id5;
        return $this;
    }

    /**
     * @return string
     */
    public function getCat5()
    {
        return $this->cat5;
    }

    /**
     * @param string $cat5
     * @return DeepSkyItem
     */
    public function setCat5($cat5)
    {
        $this->cat5 = $cat5;
        return $this;
    }

    /**
     * @return int
     */
    public function getBoringTarget()
    {
        return $this->boringTarget;
    }

    /**
     * @param int $boringTarget
     * @return DeepSkyItem
     */
    public function setBoringTarget($boringTarget)
    {
        $this->boringTarget = $boringTarget;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     * @return DeepSkyItem
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return DeepSkyItem
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return DeepSkyItem
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

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
