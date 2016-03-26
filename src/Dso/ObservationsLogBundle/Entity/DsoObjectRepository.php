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
    public function findDsosByName($criteria)
    {
        if (NULL === $criteria) {
            return array();
        }

        return $this->getEntityManager()
            ->createQuery(
                '
                SELECT o
                FROM DsoObservationsLogBundle:Object o
                WHERE (o.name LIKE :name) OR (o.otherName LIKE :otherName)
                ORDER BY o.name ASC'
            )
            ->setParameter('name', '%' . $criteria . '%')
            ->setParameter('otherName', '%' . $criteria . '%')
            ->setMaxResults(15)
            ->getResult();
    }
}
