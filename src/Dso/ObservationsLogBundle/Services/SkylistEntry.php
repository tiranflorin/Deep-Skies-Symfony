<?php

namespace Dso\ObservationsLogBundle\Services;
use Dso\ObservationsLogBundle\Entity\SkylistObject;

/**
 * Processes a SkySafari observing list entry
 * (.skylist) having the following format:
 *
 *      SkySafariObservingListVersion=3.0
 *      SortedBy=Constellation
 *      SkyObject=BeginObject
 *          ObjectID=4,0,25
 *          CommonName=Andromeda Galaxy
 *          CatalogNumber=M 31
 *          CatalogNumber=NGC 224
 *          CatalogNumber=UGC 454
 *          CatalogNumber=PGC 2557
 *          CatalogNumber=MCG 7-2-16
 *          CatalogNumber=CGCG 535-17
 *          DateObserved=2.456892452531104e+06
 *      EndObject=SkyObject
 *      ...
 *      SkyObject=BeginObject
 *          ObjectID=4,0,973
 *          CatalogNumber=NGC 404
 *          CatalogNumber=UGC 718
 *          CatalogNumber=PGC 4126
 *          CatalogNumber=MCG 6-3-18
 *          CatalogNumber=CGCG 520-20
 *          CatalogNumber=IRAS 01066+3527
 *          DateObserved=2.456892419165283e+06
 *      EndObject=SkyObject
 *
 * @package Dso\ObservationsLogBundle\Services
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class SkylistEntry {

    /** @var  string $content */
    protected $content;

    /**
     * Processes the .skylist content
     *
     * @param string $content
     *
     * @return array<Dso\ObservationsLogBundle\Entity\SkylistObject>
     */
    public function parseContent($content)
    {
        $this->content = $content;
        $this->removeHeaderInfo();
        $pieces = $this->splitIntoObservedObjects();
        $skylistObjects = $this->createFromArray($pieces);

        return $skylistObjects;
    }

    /**
     * Removes additional details like:
     *  "SkySafariObservingListVersion=3.0
     *   SortedBy=Constellation"
     */
    public function removeHeaderInfo()
    {
        $this->content = strstr($this->content, 'SkyObject=BeginObject');
    }

    /**
     * Splits the content string using a delimiter.
     *
     * @return array
     */
    public function splitIntoObservedObjects()
    {
        $pieces = explode('SkyObject=BeginObject', $this->content);

        return array_filter($pieces);
    }

    /**
     * Creates a list of observed objects.
     *
     * @param array $skylistContent
     *
     * @return array
     */
    public function createFromArray($skylistContent)
    {
        $observedObjectsList = array();
        foreach ($skylistContent as $item) {
            $skylistObject = new SkylistObject();
            $content = strstr($item, 'EndObject=SkyObject', true);
            $pieces = array_filter(explode("\n\t", $content));
            foreach ($pieces as $itemProperty) {
                if (strpos($itemProperty, 'CommonName=') !== false) {
                    trim($itemProperty);
                    $skylistObject->setCommonName(substr($itemProperty, strlen('CommonName=')));
                }
                if (strpos($itemProperty, 'CatalogNumber=NGC') !== false) {
                    trim($itemProperty);
                    $skylistObject->setCatalogNumberNgc(substr($itemProperty, strlen('CatalogNumber=')));
                }
                if (strpos($itemProperty, 'CatalogNumber=M ') !== false) {
                    trim($itemProperty);
                    $skylistObject->setCatalogNumberMessier(substr($itemProperty, strlen('CatalogNumber=')));
                }
                if (strpos($itemProperty, 'CatalogNumber=IC') !== false) {
                    trim($itemProperty);
                    $skylistObject->setCatalogNumberIc(substr($itemProperty, strlen('CatalogNumber=')));
                }
                if (strpos($itemProperty, 'DateObserved=') !== false) {
                    trim($itemProperty);
                    //TODO: Change the string value(2.456892419165283e+06) to DateTime
                    $skylistObject->setDateObserved(substr($itemProperty, strlen('DateObserved=')));
                }
                if (strpos($itemProperty, 'Comment=') !== false) {
                    trim($itemProperty);
                    $skylistObject->setComment(substr($itemProperty, strlen('Comment=')));
                }
            }
            $observedObjectsList[] = $skylistObject;
        }

        return $observedObjectsList;
    }
}
