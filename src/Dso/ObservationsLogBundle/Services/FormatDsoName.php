<?php

namespace Dso\ObservationsLogBundle\Services;

use Dso\ObservationsLogBundle\Entity\DeepSkyItem;

class FormatDsoName
{

    public function formatDsoName(DeepSkyItem $dsoDetails)
    {
        $niceName = "";
        $paranthesisOpen = false;
        $name = $dsoDetails->getName();
        $cat1 = $dsoDetails->getCat1();
        $cat2 = $dsoDetails->getCat2();
        $id1 = $dsoDetails->getId1();
        $id2 = $dsoDetails->getId2();
        if (!empty($name)) {
            $niceName = str_replace('"', '', $name);
        }
        if (!empty($cat1)) {
            if (!empty($niceName)) {
                $niceName .= ' (' . $cat1 . ' ' . $id1;
                $paranthesisOpen = true;
            } else {
                $niceName .= $cat1 . ' ' . $id1;
            }
            if (!empty($cat2)) {
                if ($paranthesisOpen) {
                    $niceName .= ', ' . $cat2 . ' ' . $id2 . ')';
                } else {
                    $niceName .= ' (' . $cat2 . ' ' . $id2 . ')';
                }
            } else {
                if ($paranthesisOpen) {
                    $niceName .= ')';
                }
            }
        }

        return $niceName;
    }
}