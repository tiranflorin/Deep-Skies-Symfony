<?php

namespace Dso\ObservationsLogBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * LoggedObjectRepository
 *
 * @package Dso\ObservationsLogBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */

class LoggedObjectRepository extends EntityRepository
{
    public function deleteByUserIdAndObsListId($userId, $obsListId)
    {
        if (empty($userId) || empty($obsListId)) {
            // TODO: maybe log something here.
            return;
        }

        $query = '
            DELETE
            FROM DsoObservationsLogBundle:LoggedObject l
            WHERE
            l.userId = :user_id AND
            l.listId = :list_id';

        $query = $this->getEntityManager()
            ->createQuery($query);
        $query->setParameter('user_id', $userId);
        $query->setParameter('list_id', $obsListId);

        return $query->execute();
    }
}
