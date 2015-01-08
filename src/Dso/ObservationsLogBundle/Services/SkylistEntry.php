<?php

namespace Dso\ObservationsLogBundle\Services;

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

    public function removeHeaderInfo($skylistFormattedString)
    {
        $processed = $skylistFormattedString;
        return $processed;
    }
} 