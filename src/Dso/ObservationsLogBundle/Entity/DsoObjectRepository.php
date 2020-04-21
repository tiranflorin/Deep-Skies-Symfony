<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * DsoObjectRepository
 *
 * @package Dso\ObservationsLogBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class DsoObjectRepository extends EntityRepository
{
    /**
     * @param string $criteria
     * @param int    $limit
     * @return array
     */
    public function findDsosByName($criteria, $limit = null)
    {
        if (NULL === $criteria) {
            return array();
        }

        $mainPart = '
            SELECT o
            FROM DsoObservationsLogBundle:DeepSkyItem o
            WHERE
            (o.name LIKE :name)
            OR (o.otherNames LIKE :otherName)';
        $orderBy = ' ORDER BY o.name, o.mag ';

        $wherePart = '';
        $parsedCriteria = $this->parseCriteria($criteria);
        if (!empty($parsedCriteria)) {
            foreach ($parsedCriteria as $conditionPair) {
                $wherePart .= $conditionPair['query'];
            }
        }
        $query = $this->getEntityManager()
            ->createQuery($mainPart . $wherePart . $orderBy);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
        $query->setParameter('name', '%' . $criteria . '%');
        $query->setParameter('otherName', '%' . $criteria . '%');

        // Bind the remaining parameters.
        if (!empty($parsedCriteria)) {
            foreach ($parsedCriteria as $conditionPair) {
                $query->setParameter($conditionPair['param']['name'], $conditionPair['param']['value'] . '%');
            }
        }

        return $query->getResult();
    }

    public function findDsosBySimpleName($name, $limit = null)
    {
        if (empty($name)) {
            return [];
        }

        $query = '
            SELECT o
            FROM DsoObservationsLogBundle:DeepSkyItem o
            WHERE
            (o.name = :name)
            OR (o.otherNames = :otherName)
            ORDER BY o.name, o.mag ';

        $query = $this->getEntityManager()
            ->createQuery($query);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }
        $query->setParameter('name', $name);
        $query->setParameter('otherName', $name);

        return $query->getResult();
    }

    public function findDsosByCatalogue($criteria, $limit = null)
    {
        if (NULL === $criteria) {
            return [];
        }

        $query = '
            SELECT o
            FROM DsoObservationsLogBundle:DeepSkyItem o
            WHERE
            (o.cat1 = :cat1 AND o.id1 = :id1) OR
            (o.cat2 = :cat2 AND o.id2 = :id2)
            ORDER BY o.name ';

        $query = $this->getEntityManager()
            ->createQuery($query);
        if (!is_null($limit)) {
            $query->setMaxResults($limit);
        }

        $parsedCriteria = $this->parseCriteria($criteria);
        if (!empty($parsedCriteria)) {
            foreach ($parsedCriteria as $conditionPair) {
                if (in_array($conditionPair['param']['name'], ['cat1', 'cat2', 'id1', 'id2'])) {
                    $query->setParameter($conditionPair['param']['name'], $conditionPair['param']['value']);
                }
            }

            return $query->getResult();
        }

        return [];


        // TODO: Maybe other queries by other categories if nothing is found
    }

    protected function getFirstNumberPositionInString($text)
    {
        preg_match('/^\D*(?=\d)/', $text, $m);

        return isset($m[0]) ? strlen($m[0]) : false;
    }

    protected function parseCriteria($criteria)
    {
        $conditionPairs = array();

        $criteria = strtolower(str_replace(' ', '', $criteria));
        $allowedAbbreviations = ['m', 'ngc', 'c', 'ic'];

        // For the case when criteria contains only catalog name.
        if (in_array($criteria, $allowedAbbreviations)) {
            $conditionPairs[] = array(
                'query' => ' OR (o.cat1 LIKE :cat1) ',
                'param' => array(
                    'name' => 'cat1',
                    'value' => $criteria
                )
            );
        }

        $pos = $this->getFirstNumberPositionInString($criteria);
        // For the case when criteria contains both catalog name and id.
        if ($pos) {
            $begin = substr($criteria, 0, $pos);
            $end = substr($criteria, $pos);

            if (in_array($begin, $allowedAbbreviations)) {
                $conditionPairs[] = array(
                    'query' => ' OR ((o.cat1 LIKE :cat1) ',
                    'param' => array(
                        'name' => 'cat1',
                        'value' => $begin
                    )
                );
                $conditionPairs[] = array(
                    'query' => ' AND (o.id1 LIKE :id1)) ',
                    'param' => array(
                        'name' => 'id1',
                        'value' => $end
                    )
                );

                $conditionPairs[] = array(
                    'query' => ' OR ((o.cat2 LIKE :cat2) ',
                    'param' => array(
                        'name' => 'cat2',
                        'value' => $begin
                    )
                );
                $conditionPairs[] = array(
                    'query' => ' AND (o.id2 LIKE :id2)) ',
                    'param' => array(
                        'name' => 'id2',
                        'value' => $end
                    )
                );
            }
        }

        return $conditionPairs;
    }

    public function findDsosByIds($dsoIds)
    {
        if (empty($dsoIds)) {
            return array();
        }

        $query = '
            SELECT o
            FROM DsoObservationsLogBundle:DeepSkyItem o
            WHERE o.id IN(:dso_ids)
            ORDER BY o.name, o.mag ';

        $query = $this->getEntityManager()
            ->createQuery($query);
        $query->setMaxResults(70);
        $query->setParameter('dso_ids', $dsoIds);

        return $query->getResult();
    }
}
