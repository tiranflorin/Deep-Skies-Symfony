<?php

namespace Dso\HomeBundle\Twig;

use Dso\ObservationsLogBundle\Entity\DeepSkyItem;
use Dso\ObservationsLogBundle\Services\FormatDsoName;

class DsoNameExtension extends \Twig_Extension
{
    /** @var FormatDsoName $formatter */
    protected $formatter;

    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('dsoName', array($this, 'formatDsoName')),
        );
    }

    public function formatDsoName($dsoDetails)
    {
        if ($dsoDetails instanceof DeepSkyItem) {
            $dso = clone $dsoDetails;
        } else {
            $dso = new DeepSkyItem();
            $dso
                ->setName($dsoDetails['name'])
                ->setCat1($dsoDetails['cat1'])
                ->setCat2($dsoDetails['cat2'])
                ->setId1($dsoDetails['id1'])
                ->setId2($dsoDetails['id2']);
        }

        return $this->formatter->formatDsoName($dso);
    }
}
